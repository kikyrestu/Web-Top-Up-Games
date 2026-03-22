<?php

declare(strict_types=1);

namespace App\Domain\Provider\Services;

use App\Domain\Provider\Contracts\ProviderAdapterInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

final class DigiflazzAdapter implements ProviderAdapterInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function syncProducts(): array
    {
        $payload = [
            'cmd' => 'prepaid',
            'username' => (string) config('services.digiflazz.username'),
            'sign' => $this->signature('pricelist'),
        ];

        $response = Http::timeout(20)
            ->acceptJson()
            ->post($this->buildUrl('/price-list'), $payload);

        if (!$response->successful()) {
            return [];
        }

        $rows = Arr::get($response->json(), 'data', []);

        if (!is_array($rows)) {
            return [];
        }

        return array_values(array_filter(array_map(static function ($row): ?array {
            if (!is_array($row)) {
                return null;
            }

            return [
                'provider_product_code' => Arr::get($row, 'buyer_sku_code'),
                'provider_product_name' => Arr::get($row, 'product_name'),
                'base_price' => (float) Arr::get($row, 'price', 0),
                'admin_fee' => 0.0,
                'commission' => 0.0,
                'raw_payload' => $row,
            ];
        }, $rows)));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createOrder(array $payload): array
    {
        $username = (string) config('services.digiflazz.username');

        $requestBody = [
            'username' => $username,
            'buyer_sku_code' => (string) ($payload['buyer_sku_code'] ?? ''),
            'customer_no' => (string) ($payload['customer_no'] ?? ''),
            'ref_id' => (string) ($payload['ref_id'] ?? ''),
            'sign' => $this->signature((string) ($payload['ref_id'] ?? '')),
        ];

        $response = Http::timeout(20)
            ->acceptJson()
            ->post($this->buildUrl('/transaction'), $requestBody);

        if (!$response->successful()) {
            return [
                'status' => 'FAILED',
                'provider_ref' => null,
                'raw' => ['http_status' => $response->status()],
            ];
        }

        $json = $response->json();
        $data = is_array($json) ? Arr::get($json, 'data', []) : [];

        $status = strtoupper((string) Arr::get($data, 'status', 'PENDING'));

        return [
            'status' => $status,
            'provider_ref' => Arr::get($data, 'ref_id'),
            'raw' => is_array($json) ? $json : [],
        ];
    }

    private function buildUrl(string $path): string
    {
        $base = rtrim((string) config('services.digiflazz.base_url'), '/');

        return $base.$path;
    }

    private function signature(string $ref): string
    {
        $username = (string) config('services.digiflazz.username');
        $apiKey = (string) config('services.digiflazz.api_key');

        return md5($username.$apiKey.$ref);
    }
}
