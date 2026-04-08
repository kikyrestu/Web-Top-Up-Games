<?php

namespace App\Services;

use App\Services\Provider\ProviderSyncInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajabillerService implements ProviderSyncInterface
{
    const BASE_URL_PROD = 'https://api.rajabiller.com/transaksi/api_json.php';
    const BASE_URL_DEV  = 'https://c-dev-api.rajabiller.com/api_json.php';

    /**
     * Build authenticated base payload for Rajabiller.
     * Credentials: uid (User ID / outlet ID) + pin (PIN transaksi).
     */
    protected function buildPayload(array $credentials, array $extra = []): array
    {
        return array_merge([
            'uid' => $credentials['uid'] ?? '',
            'pin' => $credentials['pin'] ?? '',
        ], $extra);
    }

    /**
     * Get the API endpoint URL (dev/sandbox or production).
     */
    protected function getUrl(array $credentials): string
    {
        // If env is 'sandbox', use dev URL; otherwise production
        $env = $credentials['env'] ?? 'production';
        return $env === 'sandbox' ? self::BASE_URL_DEV : self::BASE_URL_PROD;
    }

    /**
     * Send a transaction (inquiry or payment) to Rajabiller.
     * 
     * @param array  $credentials  ['uid', 'pin', 'env' (optional)]
     * @param string $method       'cek' (inquiry) | 'pay' | 'status' | 'info'
     * @param string $produk       Product code (e.g. 'PLNPASCH', 'PLNPRA500K')
     * @param string $idpel        Customer ID / meter number / phone number
     * @param string $ref1         Unique reference per transaction
     * @param array  $extra        Any extra params (e.g. periode, nominal)
     */
    public function sendTransaction(
        array $credentials,
        string $method,
        string $produk,
        string $idpel,
        string $ref1 = '',
        array $extra = []
    ): array {
        $url     = $this->getUrl($credentials);
        $payload = $this->buildPayload($credentials, array_merge([
            'method' => $method,
            'produk' => $produk,
            'idpel'  => $idpel,
            'ref1'   => $ref1,
        ], $extra));

        try {
            $response = Http::timeout(30)->withOptions(['force_ip_resolve' => 'v4'])->post($url, $payload);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('RajabillerService::sendTransaction error: ' . $e->getMessage(), compact('method', 'produk'));
            return ['rc' => '99', 'status' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Inquiry (cek tagihan) — untuk produk pascabayar (PLN, BPJS, PDAM, dll).
     */
    public function inquiry(array $credentials, string $produk, string $idpel, string $ref1 = ''): array
    {
        return $this->sendTransaction($credentials, 'cek', $produk, $idpel, $ref1);
    }

    /**
     * Payment — bayar tagihan setelah inquiry sukses.
     */
    public function pay(array $credentials, string $produk, string $idpel, string $ref1 = '', array $extra = []): array
    {
        return $this->sendTransaction($credentials, 'bayar', $produk, $idpel, $ref1, $extra);
    }

    /**
     * Pay postpaid bill — bayar tagihan pascabayar.
     * Uses same ref1 from inquiry.
     */
    public function payPostpaid(\App\Models\Transaction $transaction, string $inquiryRefId): array|false
    {
        if (! $transaction->relationLoaded('items')) {
            $transaction->load('items.apiProvider');
        }

        $item = $transaction->items->first();
        if (!$item) return false;

        $product = $item->product;
        if (!$product) return false;

        $credentials = [];
        if ($item->apiProvider) {
            $credentials = $item->apiProvider->credentials ?? [];
        }

        $produk = $item->provider_product_code ?: $product->provider_product_code;
        if (!$produk) return false;

        $idpel = $transaction->target_input;

        $result = $this->pay($credentials, $produk, $idpel, $inquiryRefId);

        Log::info('Rajabiller PayPostpaid Response', ['response' => $result]);

        $rc = $result['rc'] ?? null;
        if ($rc !== null) {
            $mappedStatus = $this->mapRcToStatus($rc);

            $updateData = ['transaction_status' => $mappedStatus];
            if (!empty($result['sn'])) {
                $updateData['sn'] = $result['sn'];
            }
            $updateData['api_response'] = json_encode($result);

            $transaction->update($updateData);

            return $result;
        }

        return false;
    }

    /**
     * Cek status transaksi via API.
     * Uses ref1 (invoice number) to check — same ref1 used when creating the transaction.
     */
    public function apiCheckStatus(array $credentials, string $ref1, string $produk = '', string $idpel = ''): array
    {
        $url     = $this->getUrl($credentials);
        $payload = $this->buildPayload($credentials, [
            'method' => 'cek_status',
            'produk' => $produk,
            'idpel'  => $idpel,
            'ref1'   => $ref1,
        ]);

        try {
            $response = Http::timeout(15)->withOptions(['force_ip_resolve' => 'v4'])->post($url, $payload);
            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('RajabillerService::apiCheckStatus error: ' . $e->getMessage());
            return ['rc' => '99', 'status' => 'Error'];
        }
    }

    public function createTransaction(\App\Models\Transaction $transaction)
    {
        if (! $transaction->relationLoaded('items')) {
            $transaction->load('items.apiProvider');
        }

        $item = $transaction->items->first();
        if (!$item) return false;

        $product = $item->product;
        if (!$product) return false;

        $credentials = [];
        if ($item->apiProvider) {
            $credentials = $item->apiProvider->credentials ?? [];
        }

        $produk = $item->provider_product_code ?: $product->provider_product_code;
        if (! $produk) return false;

        $idpel = $transaction->target_input;
        $ref1 = $transaction->invoice_number; 

        $result = $this->pay($credentials, $produk, $idpel, $ref1);

        Log::info('Rajabiller Transaction Response', ['response' => $result]);

        $rc = $result['rc'] ?? null;
        if ($rc !== null) {
            $mappedStatus = $this->mapRcToStatus($rc);

            $updateData = ['transaction_status' => $mappedStatus];
            if (!empty($result['sn'])) {
                $updateData['sn'] = $result['sn'];
            }
            $updateData['api_response'] = json_encode($result);
            
            $transaction->update($updateData);

            return $result;
        }

        return false;
    }

    public function checkStatus(\App\Models\Transaction $transaction): string
    {
        if (! $transaction->relationLoaded('items')) {
            $transaction->load('items.apiProvider');
        }

        $item = $transaction->items->first();
        if (!$item) return $transaction->transaction_status;
        
        $credentials = [];
        if ($item->apiProvider) {
            $credentials = $item->apiProvider->credentials ?? [];
        }

        $produk = $item->provider_product_code ?: ($item->product->provider_product_code ?? '');
        $idpel = $transaction->target_input;
        $ref1 = $transaction->invoice_number;

        $result = $this->apiCheckStatus($credentials, $ref1, $produk, $idpel);

        $rc = $result['rc'] ?? null;
        if ($rc !== null) {
            $mappedStatus = $this->mapRcToStatus($rc);

            if ($transaction->transaction_status !== $mappedStatus) {
                $updateData = ['transaction_status' => $mappedStatus];
                if (!empty($result['sn'])) {
                    $updateData['sn'] = $result['sn'];
                }
                $updateData['api_response'] = json_encode($result);
                $transaction->update($updateData);
            }

            return $mappedStatus;
        }

        return $transaction->transaction_status;
    }

    /**
     * Check saldo/balance.
     * Rajabiller returns saldo_akhir in every transaction response.
     * To get balance, we do a dummy info call — balance comes in the response.
     */
    public function cekSaldo(array $credentials): array
    {
        // Use info method on any known product; saldo_akhir is returned in every response
        $url     = $this->getUrl($credentials);
        $payload = $this->buildPayload($credentials, [
            'method' => 'cek',
            'produk' => 'PLNPASCH',
            'idpel'  => '000000000000',
            'ref1'   => 'SALDO' . time(),
        ]);

        try {
            $response = Http::timeout(10)->withOptions(['force_ip_resolve' => 'v4'])->post($url, $payload);
            $json = $response->json() ?? [];
            return [
                'rc' => $json['rc'] ?? '99',
                'saldo' => $json['saldo_akhir'] ?? $json['sisa_saldo'] ?? 0,
                'status' => $json['status'] ?? 'Unknown',
            ];
        } catch (\Exception $e) {
            Log::error('RajabillerService::cekSaldo error: ' . $e->getMessage());
            return ['rc' => '99', 'saldo' => 0, 'status' => 'Error'];
        }
    }

    /**
     * Get product list for sync.
     */
    public function getPriceList(array $credentials, array $options = []): array
    {
        $url     = $this->getUrl($credentials);
        $products = [];
        
        // Official Rajabiller product groups from documentation
        $groups = [
            'GAME ONLINE', 'TELKOMSEL', 'ISAT', 'AXIS / XL', 'SMART', 'KARTU3',
            'FREN', 'BOLT', 'PLN', 'EMONEY', 'PDAM', 'TV BERLANGGANAN',
            'MULTI FINANCE', 'PAJAK', 'SAMSAT', 'KARTU KREDIT',
            'TELEPON PASCA BAYAR', 'ASURANSI', 'IPL', 'EDUKASI',
        ];

        foreach ($groups as $group) {
            $payload = $this->buildPayload($credentials, [
                'method' => 'info',
                'produk' => $group
            ]);

            try {
                $response = Http::timeout(25)->withOptions(['force_ip_resolve' => 'v4'])->post($url, $payload);
                $json = $response->json();

                // Expecting array of products inside 'data' or directly in response
                $items = [];
                if (isset($json['data']) && is_array($json['data'])) {
                    $items = $json['data'];
                } elseif (isset($json['DATA']) && is_array($json['DATA'])) {
                    $items = $json['DATA'];
                } elseif (isset($json['produk']) && is_array($json['produk'])) {
                    $items = $json['produk'];
                } elseif (isset($json['PRODUK']) && is_array($json['PRODUK'])) {
                    $items = $json['PRODUK'];
                } elseif (isset($json['KODE']) || isset($json['kode']) || isset($json['id_produk'])) {
                    $items = [$json]; // Single item?
                } elseif (is_array($json)) {
                    // Try to filter array to find lists of associative arrays
                    foreach ($json as $val) {
                        if (is_array($val) && (isset($val['KODE']) || isset($val['kode']) || isset($val['code']) || isset($val['id_produk']))) {
                            $items[] = $val;
                        }
                    }
                }

                foreach ($items as $item) {
                    // Normalize keys (handle variations like KODE, kode, code)
                    $code = $item['KODE'] ?? $item['kode'] ?? $item['code'] ?? $item['KODE_PRODUK'] ?? $item['id_produk'] ?? '';
                    $name = $item['NAMA'] ?? $item['nama'] ?? $item['name'] ?? $item['NAMA_PRODUK'] ?? $item['nama_produk'] ?? 'Unknown';
                    $price = $item['HARGA'] ?? $item['harga'] ?? $item['price'] ?? $item['harga_jual'] ?? 0;
                    $statusStr = strtolower($item['STATUS'] ?? $item['status'] ?? 'aktif');
                    $needRequest = str_contains($statusStr, 'need request');
                    $isActive = !$needRequest
                        && !str_contains($statusStr, 'gangguan')
                        && !in_array($statusStr, ['nonaktif', 'kosong', '0', 'false']);

                    if ($code) {
                        $products[] = [
                            'provider_product_code' => $code,
                            'product_name' => $name,
                            'brand' => $group,
                            'category_name' => $this->parseCategory($group),
                            'type' => $this->parseType($group), // prepaid or postpaid
                            'price' => (float) $price,
                            'status_provider' => $isActive ? 'available' : 'unavailable',
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error("RajabillerService::getPriceList error for group {$group}: " . $e->getMessage());
            }
            
            // Limit loop aggression
            usleep(100000); // 100ms
        }

        return $products;
    }

    /**
     * Map Rajabiller RC code to internal transaction status.
     * Based on official Rajabiller documentation RC codes.
     */
    private function mapRcToStatus(string $rc): string
    {
        return match ($rc) {
            '00' => 'success',
            '68' => 'processing',  // Pending — sedang diproses
            '01', '03', '04', '05', '06', '08', '09', '16', '77', '87', '97' => 'failed',
            default => 'processing', // Unknown RC = treat as pending per docs
        };
    }

    /**
     * Parsing sederhana untuk tipe produk berdasarkan grupnya
     */
    private function parseType(string $group): string
    {
        $postpaidGroups = ['PDAM', 'ASURANSI', 'TV BERLANGGANAN', 'MULTI FINANCE', 'PAJAK', 'SAMSAT', 'KARTU KREDIT', 'TELEPON PASCA BAYAR', 'IPL'];
        if (in_array(strtoupper($group), $postpaidGroups)) {
            return 'postpaid';
        }
        if ($group === 'PLN') return 'prepaid';
        return 'prepaid';
    }

    /**
     * Parsing nama kategori agar sedikit lebih rapi
     */
    private function parseCategory(string $group): string
    {
        $groupUpper = strtoupper($group);
        if (in_array($groupUpper, ['TELKOMSEL', 'ISAT', 'AXIS / XL', 'SMART', 'KARTU3', 'FREN', 'BOLT'])) {
            return 'Pulsa Reguler';
        }
        return ucwords(strtolower($group));
    }
}
