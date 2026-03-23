<?php

declare(strict_types=1);

namespace App\Domain\Provider\Services;

use App\Models\OrderProviderAttempt;
use App\Models\Provider;
use Illuminate\Support\Str;

final class ProviderRouterService
{
    public function __construct(
        private readonly DigiflazzAdapter $digiflazzAdapter,
        private readonly RajabillerAdapter $rajabillerAdapter,
        private readonly OrderkuotaAdapter $orderkuotaAdapter,
    ) {
    }

    /**
     * Attempt fulfillment through ranked providers with failover.
     *
     * @param array<int, array<string, mixed>> $rankedProviders
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function dispatch(array $rankedProviders, array $payload): array
    {
        $attemptNo = 1;

        foreach ($rankedProviders as $candidate) {
            $providerCode = strtoupper((string) ($candidate['provider_code'] ?? ''));
            $providerId = (int) ($candidate['provider_id'] ?? 0);

            if ($providerCode === '') {
                continue;
            }

            $provider = Provider::query()->find($providerId);
            if ($provider === null || !$provider->is_active) {
                $attemptNo++;
                continue;
            }

            $requestPayload = [
                'buyer_sku_code' => (string) ($candidate['provider_product_code'] ?? ''),
                'customer_no' => (string) ($payload['customer_target'] ?? ''),
                'ref_id' => (string) ($payload['order_code'] ?? 'ORD').'-'.Str::upper(Str::random(4)),
            ];

            if ($requestPayload['buyer_sku_code'] === '') {
                OrderProviderAttempt::query()->create([
                    'order_id' => (int) ($payload['order_id'] ?? 0),
                    'provider_id' => $providerId,
                    'attempt_no' => $attemptNo,
                    'status' => 'FAILED',
                    'provider_ref' => null,
                    'request_payload' => $requestPayload,
                    'response_payload' => ['error' => 'missing_provider_product_code'],
                    'attempted_at' => now(),
                ]);

                $attemptNo++;
                continue;
            }

            $response = $this->sendToProvider($providerCode, $requestPayload);

            OrderProviderAttempt::query()->create([
                'order_id' => (int) ($payload['order_id'] ?? 0),
                'provider_id' => $providerId,
                'attempt_no' => $attemptNo,
                'status' => strtoupper((string) ($response['status'] ?? 'PENDING')),
                'provider_ref' => $response['provider_ref'] ?? null,
                'request_payload' => $requestPayload,
                'response_payload' => $response,
                'attempted_at' => now(),
            ]);

            $status = strtoupper((string) ($response['status'] ?? 'PENDING'));
            if (in_array($status, ['SUCCESS', 'PAID', 'PENDING'], true)) {
                return $response;
            }

            $attemptNo++;
        }

        return [
            'status' => 'FAILED',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sendToProvider(string $providerCode, array $payload): array
    {
        return match ($providerCode) {
            'DIGIFLAZZ' => $this->digiflazzAdapter->createOrder($payload),
            'RAJABILLER' => $this->rajabillerAdapter->createOrder($payload),
            'ORDERKUOTA' => $this->orderkuotaAdapter->createOrder($payload),
            default => [
                'status' => 'FAILED',
                'provider_ref' => null,
                'raw' => ['error' => 'provider_adapter_not_implemented'],
            ],
        };
    }
}
