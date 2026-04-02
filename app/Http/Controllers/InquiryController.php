<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\DigiflazzService;
use App\Services\RajabillerService;
use App\Services\Provider\ProviderSyncFactory;
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

        // Resolve cheapest active provider mapping
        $selectedMapping = $product->resolveCheapestProviderMapping();
        if (!$selectedMapping) {
            return response()->json([
                'success' => false,
                'message' => 'Produk belum memiliki provider aktif.',
            ], 422);
        }

        $provider = $selectedMapping->apiProvider;
        if (!$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Provider tidak ditemukan.',
            ], 422);
        }

        $providerCode = strtolower($provider->code);
        $buyerSkuCode = $selectedMapping->provider_product_code;
        $customerNo   = $request->customer_no;

        try {
            $result = match ($providerCode) {
                'digiflazz'  => $this->inquiryDigiflazz($provider, $buyerSkuCode, $customerNo),
                'rajabiller'  => $this->inquiryRajabiller($provider, $buyerSkuCode, $customerNo),
                default       => ['success' => false, 'message' => 'Provider tidak mendukung fitur cek tagihan.'],
            };

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Inquiry Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengecek tagihan. Silakan coba lagi.',
            ], 500);
        }
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

        return [
            'success'       => $success,
            'ref_id'        => $ref1,
            'customer_no'   => $result['idpel'] ?? $idpel,
            'customer_name' => $result['nama'] ?? $result['customer_name'] ?? '',
            'buyer_sku_code'=> $produk,
            'price'         => (int) ($result['tagihan'] ?? $result['nominal'] ?? $result['price'] ?? 0),
            'admin'         => (int) ($result['admin'] ?? 0),
            'periode'       => $result['periode'] ?? '',
            'message'       => $result['status'] ?? $result['message'] ?? '',
            'status'        => $success ? 'Sukses' : 'Gagal',
            'rc'            => $rc,
            'desc'          => $result['desc'] ?? [],
            'provider'      => 'rajabiller',
        ];
    }
}
