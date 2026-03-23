<?php

declare(strict_types=1);

namespace App\Domain\Provider\Services;

use App\Domain\Provider\Contracts\ProviderAdapterInterface;
use App\Domain\Provider\Support\ProviderStatusNormalizer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Throwable;

final class RajabillerAdapter implements ProviderAdapterInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function syncProducts(): array
    {
        $baseUrl = rtrim((string) config('services.rajabiller.base_url'), '/');
        $username = (string) config('services.rajabiller.username');
        $apiKey = (string) config('services.rajabiller.api_key');

        if ($baseUrl === '' || $username === '' || $apiKey === '') {
            return [];
        }

        $response = Http::timeout(20)
            ->acceptJson()
            ->post($baseUrl.'/products', [
                'username' => $username,
                'api_key' => $apiKey,
            ]);

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
                'provider_product_code' => (string) Arr::get($row, 'code', ''),
                'provider_product_name' => (string) Arr::get($row, 'name', ''),
                'base_price' => (float) Arr::get($row, 'price', 0),
                'admin_fee' => (float) Arr::get($row, 'admin_fee', 0),
                'commission' => (float) Arr::get($row, 'commission', 0),
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
        $baseUrl = rtrim((string) config('services.rajabiller.base_url'), '/');
        $username = (string) config('services.rajabiller.username');
        $apiKey = (string) config('services.rajabiller.api_key');

        if ($baseUrl === '' || $username === '' || $apiKey === '') {
            return [
                'status' => 'FAILED',
                'is_retryable' => false,
                'provider_ref' => null,
                'raw' => ['error' => 'missing_rajabiller_config'],
            ];
        }

        if ((string) ($payload['buyer_sku_code'] ?? '') === '' || (string) ($payload['customer_no'] ?? '') === '') {
            return [
                'status' => 'FAILED',
                'is_retryable' => false,
                'provider_ref' => null,
                'raw' => ['error' => 'invalid_payload'],
            ];
        }

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->post($baseUrl.'/transaction', [
                    'username' => $username,
                    'api_key' => $apiKey,
                    'product_code' => (string) $payload['buyer_sku_code'],
                    'customer_no' => (string) $payload['customer_no'],
                    'ref_id' => (string) ($payload['ref_id'] ?? ''),
                ]);
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
                'raw' => ['http_status' => $response->status()],
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
}
