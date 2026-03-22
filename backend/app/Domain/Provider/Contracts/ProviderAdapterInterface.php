<?php

declare(strict_types=1);

namespace App\Domain\Provider\Contracts;

interface ProviderAdapterInterface
{
    /**
     * Pull provider product catalog and normalized pricing payload.
     *
     * @return array<int, array<string, mixed>>
     */
    public function syncProducts(): array;

    /**
     * Send order payload to provider and return raw result.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createOrder(array $payload): array;
}
