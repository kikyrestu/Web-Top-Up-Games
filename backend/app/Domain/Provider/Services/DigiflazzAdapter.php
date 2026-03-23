<?php

declare(strict_types=1);

namespace App\Domain\Provider\Services;

use App\Domain\Provider\Contracts\ProviderAdapterInterface;
use App\Domain\Provider\Support\ProviderStatusNormalizer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Throwable;

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
        if ((string) ($payload['buyer_sku_code'] ?? '') === '' || (string) ($payload['customer_no'] ?? '') === '') {
            return [
                'status' => 'FAILED',
                'is_retryable' => false,
                'provider_ref' => null,
                'raw' => ['error' => 'invalid_payload'],
            ];
        }

        $username = (string) config('services.digiflazz.username');

        $requestBody = [
            'username' => $username,
            'buyer_sku_code' => (string) ($payload['buyer_sku_code'] ?? ''),
            'customer_no' => (string) ($payload['customer_no'] ?? ''),
            'ref_id' => (string) ($payload['ref_id'] ?? ''),
            'sign' => $this->signature((string) ($payload['ref_id'] ?? '')),
        ];

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->post($this->buildUrl('/transaction'), $requestBody);
        } catch (Throwable $exception) {
            return [
                'status' => 'FAILED',
                'is_retryable' => true,
                'provider_ref' => null,
                'raw' => [
                    'error' => 'network_exception',
                    'message' => $exception->getMessage(),
                ],
            ];
        }

        if (!$response->successful()) {
            return [
                'status' => 'FAILED',
                'is_retryable' => ProviderStatusNormalizer::isRetryableHttpStatus($response->status()),
                'provider_ref' => null,
                'raw' => [
                    'error' => 'http_status_'.$response->status(),
                    'http_status' => $response->status(),
                ],
            ];
        }

        $json = $response->json();
        $data = is_array($json) ? Arr::get($json, 'data', []) : [];
        $normalized = ProviderStatusNormalizer::normalize(Arr::get($data, 'status', 'PENDING'));

        return [
            'status' => $normalized['status'],
            'is_retryable' => $normalized['is_retryable'],
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
