<?php

namespace App\Services;

use App\Services\Provider\ProviderSyncInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RajabillerService implements ProviderSyncInterface
{
    /**
     * Get price list from Rajabiller API.
     * NOTE: Update this method with actual Rajabiller API docs when ready.
     */
    public function getPriceList(array $credentials, array $options = []): array
    {
        $userId   = $credentials['user_id'] ?? '';
        $password = $credentials['password'] ?? '';
        $baseUrl  = $credentials['url'] ?? 'https://rajabiller.fastpay.co.id/api/json/';

        try {
            $response = Http::post($baseUrl, [
                'uid'    => $userId,
                'pin'    => $password,
                'method' => 'rajabiller.pricelist',
            ]);

            $result = $response->json();

            if (!isset($result['data']) || !is_array($result['data'])) {
                Log::warning('Rajabiller price-list: no data returned', ['response' => $result]);
                return [];
            }

            return collect($result['data'])->map(function ($item) {
                return [
                    'provider_product_code' => $item['kode_produk'] ?? $item['code'] ?? '',
                    'product_name'          => $item['nama_produk'] ?? $item['product'] ?? '',
                    'brand'                 => $item['operator'] ?? $item['brand'] ?? '',
                    'category_name'         => $item['kategori'] ?? $item['category'] ?? '',
                    'type'                  => strtolower($item['tipe'] ?? 'prepaid'),
                    'price'                 => (float) ($item['harga'] ?? $item['price'] ?? 0),
                    'status_provider'       => strtolower($item['status'] ?? 'available'),
                ];
            })->filter(fn($item) => !empty($item['provider_product_code']))->values()->all();

        } catch (\Exception $e) {
            Log::error('Rajabiller getPriceList error: ' . $e->getMessage());
            return [];
        }
    }
}
