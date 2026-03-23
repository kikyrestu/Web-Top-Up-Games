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
        private readonly ProviderCircuitBreakerService $circuitBreakerService,
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
        $maxRetriesPerProvider = max(0, (int) config('services.provider_router.max_retries_per_provider', 1));
        $lastFailure = [
            'is_retryable' => false,
            'raw' => ['error' => 'provider_dispatch_failed'],
        ];

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

            if ($this->circuitBreakerService->isProviderBlocked($providerId)) {
                OrderProviderAttempt::query()->create([
                    'order_id' => (int) ($payload['order_id'] ?? 0),
                    'provider_id' => $providerId,
                    'attempt_no' => $attemptNo,
                    'status' => 'SKIPPED',
                    'provider_ref' => null,
                    'request_payload' => [
                        'reason' => 'circuit_breaker_open',
                    ],
                    'response_payload' => [
                        'status' => 'FAILED',
                        'is_retryable' => false,
                        'raw' => ['error' => 'provider_temporarily_blocked'],
                    ],
                    'attempted_at' => now(),
                ]);

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

            for ($retry = 0; $retry <= $maxRetriesPerProvider; $retry++) {
                $response = $this->sendToProvider($providerCode, $requestPayload);
                $status = strtoupper((string) ($response['status'] ?? 'PENDING'));
                $isRetryable = (bool) ($response['is_retryable'] ?? false);

                if (in_array($status, ['FAILED', 'ERROR'], true)) {
                    $lastFailure = [
                        'is_retryable' => $isRetryable,
                        'raw' => is_array($response['raw'] ?? null)
                            ? $response['raw']
                            : ['error' => 'provider_dispatch_failed'],
                    ];
                }

                OrderProviderAttempt::query()->create([
                    'order_id' => (int) ($payload['order_id'] ?? 0),
                    'provider_id' => $providerId,
                    'attempt_no' => $attemptNo,
                    'status' => $status,
                    'provider_ref' => $response['provider_ref'] ?? null,
                    'request_payload' => array_merge($requestPayload, [
                        'retry_index' => $retry,
                        'max_retries' => $maxRetriesPerProvider,
                    ]),
                    'response_payload' => $response,
                    'attempted_at' => now(),
                ]);

                $this->circuitBreakerService->recordAttempt($providerId, $status);

                if (in_array($status, ['SUCCESS', 'PAID', 'PENDING'], true)) {
                    return $response;
                }

                $hasRetryLeft = $retry < $maxRetriesPerProvider;

                $attemptNo++;

                if (!$isRetryable || !$hasRetryLeft) {
                    break;
                }
            }
        }

        return [
            'status' => 'FAILED',
            'is_retryable' => (bool) ($lastFailure['is_retryable'] ?? false),
            'raw' => is_array($lastFailure['raw'] ?? null)
                ? $lastFailure['raw']
                : ['error' => 'provider_dispatch_failed'],
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
