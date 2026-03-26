<?php

namespace App\Services;

use App\Services\Provider\ProviderSyncInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderKuotaService implements ProviderSyncInterface
{
    /**
     * Get price list from OrderKuota API.
     * NOTE: Update this method with actual OrderKuota API docs when ready.
     */
    public function getPriceList(array $credentials, array $options = []): array
    {
        $apiKey  = $credentials['api_key'] ?? '';
        $baseUrl = $credentials['url'] ?? 'https://api.orderkuota.com/v1/';

        try {
            $response = Http::withToken($apiKey)->get($baseUrl . 'product/list');
            $result   = $response->json();

            if (!isset($result['data']) || !is_array($result['data'])) {
                Log::warning('OrderKuota price-list: no data returned', ['response' => $result]);
                return [];
            }

            return collect($result['data'])->map(function ($item) {
                return [
                    'provider_product_code' => $item['code'] ?? $item['sku'] ?? '',
                    'product_name'          => $item['name'] ?? $item['product_name'] ?? '',
                    'brand'                 => $item['brand'] ?? $item['operator'] ?? '',
                    'category_name'         => $item['category'] ?? '',
                    'type'                  => strtolower($item['type'] ?? 'prepaid'),
                    'price'                 => (float) ($item['price'] ?? 0),
                    'status_provider'       => strtolower($item['status'] ?? 'available'),
                ];
            })->filter(fn($item) => !empty($item['provider_product_code']))->values()->all();

        } catch (\Exception $e) {
            Log::error('OrderKuota getPriceList error: ' . $e->getMessage());
            return [];
        }
    }
}
