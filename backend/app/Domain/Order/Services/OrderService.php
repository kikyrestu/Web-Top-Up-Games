<?php

declare(strict_types=1);

namespace App\Domain\Order\Services;

final class OrderService
{
    /**
     * Create order in PENDING state and return canonical order data.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        // TODO: Implement idempotent order creation.
        return $payload;
    }
}
