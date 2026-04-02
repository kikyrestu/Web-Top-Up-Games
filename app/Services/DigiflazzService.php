<?php

namespace App\Services;

use App\Models\ApiProvider;
use App\Models\Transaction;
use App\Services\Provider\ProviderSyncInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DigiflazzService implements ProviderSyncInterface
{
    protected $username;
    protected $apiKey;
    protected $baseUrl = 'https://api.digiflazz.com/v1/';

    public function __construct()
    {
        $provider = ApiProvider::where('code', 'digiflazz')
            ->orWhere('name', 'Digiflazz')
            ->first();

        $this->applyCredentialsFromProvider($provider);
    }

    public function createTransaction(Transaction $transaction)
    {
        // Get the first item (assuming 1 transaction = 1 item generally for PPOB)
        if (! $transaction->relationLoaded('items')) {
            $transaction->load('items.apiProvider');
        }

        $item = $transaction->items->first();
        if (!$item) return false;

        $product = $item->product;
        if (!$product) return false;

        if ($item->apiProvider) {
            $this->applyCredentialsFromProvider($item->apiProvider);
        }

        $buyerSkuCode = $item->provider_product_code ?: $product->provider_product_code;
        if (! $buyerSkuCode) {
            Log::warning('Digiflazz aborted: provider product code is empty.', ['invoice' => $transaction->invoice_number]);
            return false;
        }

        $customerNo = $transaction->target_input;
        $refId = $transaction->invoice_number; 

        $sign = md5($this->username . $this->apiKey . $refId);

        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => $buyerSkuCode,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign,
        ];

        try {
            $response = Http::post($this->baseUrl . 'transaction', $payload);
            $result = $response->json();

            // Digiflazz format usually returns 'data' on success or error.
            Log::info('Digiflazz Transaction Response', ['payload' => $payload, 'response' => $result]);

            if (isset($result['data']['status'])) {
                $status = $result['data']['status']; // Pending, Sukses, Gagal
                
                // Update transaction status based on API response
                $statusMap = [
                    'Pending' => 'processing',
                    'Sukses' => 'success',
                    'Gagal' => 'failed'
                ];

                $transaction->update([
                    'transaction_status' => $statusMap[$status] ?? 'processing'
                ]);

                return $result['data'];
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Digiflazz Exception: ' . $e->getMessage());
            return false;
        }
    }

    protected function applyCredentialsFromProvider(?ApiProvider $provider): void
    {
        if (! $provider || ! $provider->credentials) {
            return;
        }

        $creds = $provider->credentials;
        $this->username = $creds['username'] ?? '';
        $this->apiKey = $creds['api_key'] ?? '';

        if (isset($creds['url']) && ! empty($creds['url'])) {
            $this->baseUrl = rtrim($creds['url'], '/') . '/';
        }
    }

    /**
     * Get full price list from Digiflazz API.
     * Implements ProviderSyncInterface.
     */
    public function getPriceList(array $credentials, array $options = []): array
    {
        $username = $credentials['username'] ?? $this->username;
        $apiKey   = $credentials['api_key'] ?? $this->apiKey;
        $baseUrl  = isset($credentials['url']) && !empty($credentials['url'])
            ? rtrim($credentials['url'], '/') . '/'
            : $this->baseUrl;

        $cmd  = $options['cmd'] ?? 'prepaid';
        $sign = md5($username . $apiKey . 'pricelist');

        $payload = [
            'cmd'      => $cmd,
            'username' => $username,
            'sign'     => $sign,
        ];

        try {
            $response = Http::timeout(30)->post($baseUrl . 'price-list', $payload);
            $result   = $response->json();

            if (!isset($result['data']) || !is_array($result['data'])) {
                Log::warning('Digiflazz price-list: no data returned', ['response' => $result]);
                return [];
            }

            return collect($result['data'])->map(function ($item) use ($cmd) {
                // buyer_product_status is a boolean in Digiflazz API
                $isActive = (bool) ($item['buyer_product_status'] ?? true);

                return [
                    'provider_product_code' => $item['buyer_sku_code'] ?? '',
                    'product_name'          => $item['product_name'] ?? '',
                    'brand'                 => $item['brand'] ?? '',
                    'category_name'         => $item['category'] ?? '',
                    'type'                  => $cmd === 'pasca' ? 'postpaid' : 'prepaid',
                    'price'                 => (float) ($item['price'] ?? 0),
                    'status_provider'       => $isActive ? 'available' : 'unavailable',
                    'description'           => $item['desc'] ?? '',
                    'start_cut_off'         => $item['start_cut_off'] ?? null,
                    'end_cut_off'           => $item['end_cut_off'] ?? null,
                ];
            })->filter(function ($item) {
                return !empty($item['provider_product_code']);
            })->values()->all();

        } catch (\Exception $e) {
            Log::error('Digiflazz getPriceList error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Inquiry (cek tagihan) — untuk produk pascabayar.
     * Digiflazz pasca: POST /v1/transaction with commands = 'inq-pasca'
     */
    public function inquiry(string $buyerSkuCode, string $customerNo, ?ApiProvider $provider = null): array
    {
        if ($provider) {
            $this->applyCredentialsFromProvider($provider);
        }

        $refId = 'INQ-' . strtoupper(\Illuminate\Support\Str::random(8)) . time();
        $sign  = md5($this->username . $this->apiKey . $refId);

        $payload = [
            'commands'       => 'inq-pasca',
            'username'       => $this->username,
            'buyer_sku_code' => $buyerSkuCode,
            'customer_no'    => $customerNo,
            'ref_id'         => $refId,
            'sign'           => $sign,
        ];

        try {
            $response = Http::timeout(30)->post($this->baseUrl . 'transaction', $payload);
            $result   = $response->json();

            Log::info('Digiflazz Inquiry Response', ['payload' => $payload, 'response' => $result]);

            $data = $result['data'] ?? $result;

            return [
                'success'       => ($data['status'] ?? '') === 'Sukses' && ($data['rc'] ?? '') === '00',
                'ref_id'        => $data['ref_id'] ?? $refId,
                'customer_no'   => $data['customer_no'] ?? $customerNo,
                'customer_name' => $data['customer_name'] ?? '',
                'buyer_sku_code'=> $data['buyer_sku_code'] ?? $buyerSkuCode,
                'price'         => (int) ($data['selling_price'] ?? $data['price'] ?? 0),
                'admin'         => (int) ($data['admin'] ?? 0),
                'periode'       => $data['periode'] ?? '',
                'message'       => $data['message'] ?? '',
                'status'        => $data['status'] ?? 'Gagal',
                'rc'            => $data['rc'] ?? '99',
                'desc'          => $data['desc'] ?? [],
                'provider'      => 'digiflazz',
            ];
        } catch (\Exception $e) {
            Log::error('Digiflazz Inquiry Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menghubungi provider: ' . $e->getMessage(),
                'provider' => 'digiflazz',
            ];
        }
    }

    /**
     * Pay postpaid bill — bayar tagihan pascabayar.
     * Uses same ref_id from inquiry, commands = 'pay-pasca'
     */
    public function payPostpaid(Transaction $transaction, string $inquiryRefId): array|false
    {
        if (! $transaction->relationLoaded('items')) {
            $transaction->load('items.apiProvider');
        }

        $item = $transaction->items->first();
        if (!$item) return false;

        $product = $item->product;
        if (!$product) return false;

        if ($item->apiProvider) {
            $this->applyCredentialsFromProvider($item->apiProvider);
        }

        $buyerSkuCode = $item->provider_product_code ?: $product->provider_product_code;
        if (!$buyerSkuCode) return false;

        $customerNo = $transaction->target_input;
        $sign = md5($this->username . $this->apiKey . $inquiryRefId);

        $payload = [
            'commands'       => 'pay-pasca',
            'username'       => $this->username,
            'buyer_sku_code' => $buyerSkuCode,
            'customer_no'    => $customerNo,
            'ref_id'         => $inquiryRefId,
            'sign'           => $sign,
        ];

        try {
            $response = Http::timeout(30)->post($this->baseUrl . 'transaction', $payload);
            $result   = $response->json();

            Log::info('Digiflazz PayPostpaid Response', ['payload' => $payload, 'response' => $result]);

            $data = $result['data'] ?? $result;

            if (isset($data['status'])) {
                $statusMap = [
                    'Pending' => 'processing',
                    'Sukses'  => 'success',
                    'Gagal'   => 'failed',
                ];
                $mappedStatus = $statusMap[$data['status']] ?? 'processing';

                $updateData = ['transaction_status' => $mappedStatus];
                if (!empty($data['sn'])) {
                    $updateData['sn'] = $data['sn'];
                }

                $transaction->update($updateData);

                return $data;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Digiflazz PayPostpaid Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function checkStatus(Transaction $transaction): string
    {
        if (! $transaction->relationLoaded('items')) {
            $transaction->load('items.apiProvider');
        }

        $item = $transaction->items->first();
        if (!$item) return $transaction->transaction_status;

        $product = $item->product;
        if (!$product) return $transaction->transaction_status;

        if ($item->apiProvider) {
            $this->applyCredentialsFromProvider($item->apiProvider);
        }

        $buyerSkuCode = $item->provider_product_code ?: $product->provider_product_code;
        if (! $buyerSkuCode) return $transaction->transaction_status;

        $customerNo = $transaction->target_input;
        $refId = $transaction->invoice_number; 
        $sign = md5($this->username . $this->apiKey . $refId);

        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => $buyerSkuCode,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign,
        ];

        try {
            $response = Http::post($this->baseUrl . 'transaction', $payload);
            $result = $response->json();

            if (isset($result['data']['status'])) {
                $statusMap = [
                    'Pending' => 'processing',
                    'Sukses' => 'success',
                    'Gagal' => 'failed'
                ];
                
                $mappedStatus = $statusMap[$result['data']['status']] ?? 'processing';
                
                if ($transaction->transaction_status !== $mappedStatus) {
                    $updateData = ['transaction_status' => $mappedStatus];
                    
                    if (isset($result['data']['sn']) && !empty($result['data']['sn'])) {
                        $updateData['sn'] = $result['data']['sn'];
                    }
                    if (isset($result['data']['note']) && !empty($result['data']['note'])) {
                        $updateData['api_response'] = $result['data']['note'];
                    }
                    
                    $transaction->update($updateData);
                }

                return $mappedStatus;
            }

            return $transaction->transaction_status;

        } catch (\Exception $e) {
            Log::error('Digiflazz checkStatus Exception: ' . $e->getMessage());
            return $transaction->transaction_status;
        }
    }
}