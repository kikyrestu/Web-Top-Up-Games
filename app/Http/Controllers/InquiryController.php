<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\DigiflazzService;
use App\Services\RajabillerService;
use App\Services\Provider\ProviderSyncFactory;
use App\Services\OrderKuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class InquiryController extends Controller
{
    /**
     * Cek Tagihan (Bill Inquiry) for postpaid products.
     * POST /api/inquiry
     */
    public function inquiry(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'customer_no'  => 'required|string|min:4|max:30',
        ]);

        // Rate limit: max 5 inquiries per minute per IP
        $rateLimitKey = 'inquiry:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak permintaan. Coba lagi dalam ' . RateLimiter::availableIn($rateLimitKey) . ' detik.',
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 60);

        $product = Product::with('providerMappings.apiProvider', 'category')
            ->findOrFail($request->product_id);

        // Get all active provider mappings, sorted by priority
        $mappings = $product->providerMappings
            ->filter(fn($m) => $m->is_active && $m->apiProvider && $m->apiProvider->is_active)
            ->sortBy([['priority', 'asc'], ['price_capital', 'asc'], ['id', 'asc']]);

        if ($mappings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Produk belum memiliki provider aktif.',
            ], 422);
        }

        $customerNo = $request->customer_no;
        $lastResult = null;

        // Try each provider until one succeeds
        foreach ($mappings as $mapping) {
            $provider     = $mapping->apiProvider;
            $providerCode = strtolower($provider->code);
            $buyerSkuCode = $mapping->provider_product_code;

            try {
                $result = match ($providerCode) {
                    'digiflazz'   => $this->inquiryDigiflazz($provider, $buyerSkuCode, $customerNo),
                    'rajabiller'  => $this->inquiryRajabiller($provider, $buyerSkuCode, $customerNo),
                    'orderkuota'  => $this->inquiryOkeConnect($provider, $buyerSkuCode, $customerNo),
                    default       => null,
                };

                if (!$result) continue;

                $lastResult = $result;

                if (!empty($result['success'])) {
                    return response()->json($result);
                }

                // If failure is customer-specific (wrong number), don't try other providers
                $rc = $result['rc'] ?? '';
                $msg = strtolower($result['message'] ?? '');
                $isCustomerError = in_array($rc, ['03', '14', '20', '21'])
                    || str_contains($msg, 'invalid number')
                    || str_contains($msg, 'nomor tujuan salah')
                    || str_contains($msg, 'id pelanggan tidak ditemukan')
                    || str_contains($msg, 'nomor tidak ditemukan');

                if ($isCustomerError) {
                    return response()->json($result);
                }

                Log::info("Inquiry fallback: {$provider->name} failed (rc={$rc}), trying next provider...");

            } catch (\Exception $e) {
                Log::warning("Inquiry {$provider->name} error: " . $e->getMessage() . ', trying next...');
                continue;
            }
        }

        // All providers failed — return last result or generic error
        return response()->json($lastResult ?? [
            'success' => false,
            'message' => 'Semua provider gagal mengecek tagihan. Silakan coba lagi nanti.',
        ]);
    }

    private function inquiryDigiflazz($provider, string $buyerSkuCode, string $customerNo): array
    {
        $service = app(DigiflazzService::class);
        return $service->inquiry($buyerSkuCode, $customerNo, $provider);
    }

    private function inquiryRajabiller($provider, string $produk, string $idpel): array
    {
        $service     = app(RajabillerService::class);
        $credentials = $provider->credentials ?? [];
        $ref1        = 'INQ-' . strtoupper(\Illuminate\Support\Str::random(8)) . time();

        $result = $service->inquiry($credentials, $produk, $idpel, $ref1);

        Log::info('Rajabiller Inquiry Response', ['response' => $result]);

        $rc = $result['rc'] ?? '99';
        $success = $rc === '00';

        // Build desc from Rajabiller's structured fields
        $desc = $this->buildRajabillerDesc($result);

        return [
            'success'       => $success,
            'ref_id'        => $ref1,
            'customer_no'   => $result['idpel'] ?? $idpel,
            'customer_name' => $result['nama'] ?? $result['customer_name'] ?? '',
            'buyer_sku_code'=> $produk,
            'price'         => (int) ($result['tagihan'] ?? $result['nominal'] ?? $result['price'] ?? 0),
            'admin'         => (int) ($result['adm'] ?? $result['admin'] ?? 0),
            'periode'       => $result['periode'] ?? $result['blth'] ?? '',
            'message'       => $result['status'] ?? $result['message'] ?? '',
            'status'        => $success ? 'Sukses' : 'Gagal',
            'rc'            => $rc,
            'desc'          => $desc,
            'provider'      => 'rajabiller',
        ];
    }

    private function buildRajabillerDesc(array $result): array
    {
        $desc = [];

        // PLN fields
        if (!empty($result['tarif'])) $desc['tarif'] = $result['tarif'];
        if (!empty($result['daya'])) $desc['daya'] = $result['daya'];
        if (!empty($result['alamat'])) $desc['alamat'] = $result['alamat'];
        if (!empty($result['lembar_tagihan'])) $desc['lembar_tagihan'] = $result['lembar_tagihan'];

        // Multi Finance fields
        if (!empty($result['angsuran_ke'])) {
            $desc['angsuran'] = 'Ke-' . ltrim($result['angsuran_ke'], '0');
        }
        if (!empty($result['tenor'])) {
            $desc['tenor'] = ltrim($result['tenor'], '0') . ' bulan';
        }
        if (!empty($result['no_polisi'])) {
            $desc['no_polisi'] = $result['no_polisi'];
        }
        if (!empty($result['merk_type'])) {
            $desc['merk_type'] = $result['merk_type'];
        }

        // PLN detail tagihan (multi-period)
        if (!empty($result['detail_tagihan']) && is_array($result['detail_tagihan'])) {
            $desc['detail'] = array_map(fn($d) => [
                'periode'        => $d['blth'] ?? $d['periode'] ?? '-',
                'nilai_tagihan'  => (int) ($d['tagihan'] ?? $d['nilai_tagihan'] ?? 0),
            ], $result['detail_tagihan']);
        }

        return $desc;
    }

    private function inquiryOkeConnect($provider, string $productCode, string $customerNo): array
    {
        $service     = app(OrderKuotaService::class);
        $credentials = $provider->credentials ?? [];

        $result = $service->inquiry($credentials, $productCode, $customerNo);

        Log::info('OkeConnect Inquiry Response', ['response' => $result]);

        return $result;
    }
}
