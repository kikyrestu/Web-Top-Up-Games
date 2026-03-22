<?php

declare(strict_types=1);

namespace App\Domain\Provider\Services;

use App\Domain\Provider\Contracts\ProviderAdapterInterface;

final class DigiflazzAdapter implements ProviderAdapterInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function syncProducts(): array
    {
        // TODO: Implement Digiflazz product sync via HTTP client.
        return [];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createOrder(array $payload): array
    {
        // TODO: Implement Digiflazz order request mapping and response normalization.
        return [
            'status' => 'PENDING',
            'provider_ref' => null,
            'raw' => [],
        ];
    }
}
