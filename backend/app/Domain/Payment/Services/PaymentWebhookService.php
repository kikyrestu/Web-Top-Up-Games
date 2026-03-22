<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

final class PaymentWebhookService
{
    /**
     * Verify webhook signature and normalize event data.
     *
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     * @return array<string, mixed>
     */
    public function handle(array $payload, array $headers): array
    {
        // TODO: Add gateway-specific signature verification and idempotency.
        return [
            'verified' => false,
            'payload' => $payload,
        ];
    }
}
