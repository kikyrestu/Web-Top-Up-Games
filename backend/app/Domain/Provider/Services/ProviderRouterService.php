<?php

declare(strict_types=1);

namespace App\Domain\Provider\Services;

final class ProviderRouterService
{
    /**
     * Attempt fulfillment through ranked providers with failover.
     *
     * @param array<int, array<string, mixed>> $rankedProviders
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function dispatch(array $rankedProviders, array $payload): array
    {
        // TODO: Implement retry/failover and structured attempt logging.
        return [
            'status' => 'PENDING',
        ];
    }
}
