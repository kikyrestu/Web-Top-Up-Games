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
            $response = Http::post($baseUrl . 'price-list', $payload);
            $result   = $response->json();

            if (!isset($result['data']) || !is_array($result['data'])) {
                Log::warning('Digiflazz price-list: no data returned', ['response' => $result]);
                return [];
            }

            return collect($result['data'])->map(function ($item) use ($cmd) {
                return [
                    'provider_product_code' => $item['buyer_sku_code'] ?? '',
                    'product_name'          => $item['product_name'] ?? '',
                    'brand'                 => $item['brand'] ?? '',
                    'category_name'         => $item['category'] ?? '',
                    'type'                  => $cmd === 'pasca' ? 'pasca' : 'prepaid',
                    'price'                 => (float) ($item['price'] ?? 0),
                    'status_provider'       => strtolower($item['buyer_product_status'] ?? 'available'),
                ];
            })->filter(function ($item) {
                return !empty($item['provider_product_code']);
            })->values()->all();

        } catch (\Exception $e) {
            Log::error('Digiflazz getPriceList error: ' . $e->getMessage());
            return [];
        }
    }
}