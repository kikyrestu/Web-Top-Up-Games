<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Jobs\FulfillPaidOrderJob;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $gateway = strtoupper((string) Arr::get($payload, 'gateway', 'UNKNOWN'));
        $eventKey = (string) Arr::get($payload, 'event_key', '');
        $orderCode = (string) Arr::get($payload, 'order_code', '');
        $gatewayReference = (string) Arr::get($payload, 'gateway_reference', '');
        $status = strtoupper((string) Arr::get($payload, 'status', 'PENDING'));
        $amount = (float) Arr::get($payload, 'amount', 0);
        $method = Arr::get($payload, 'method');

        if ($eventKey !== '') {
            $existingEvent = PaymentWebhook::query()
                ->where('gateway', $gateway)
                ->where('event_key', $eventKey)
                ->first();

            if ($existingEvent !== null) {
                return [
                    'verified' => true,
                    'duplicate' => true,
                    'processed' => false,
                    'code' => 'WEBHOOK_DUPLICATE_EVENT',
                ];
            }
        }

        $verification = $this->verify($gateway, $payload, $headers);

        if (!(bool) ($verification['verified'] ?? false)) {
            return [
                'verified' => false,
                'duplicate' => false,
                'processed' => false,
                'code' => (string) ($verification['code'] ?? 'WEBHOOK_INVALID_SIGNATURE'),
            ];
        }

        if ($orderCode === '' || $gatewayReference === '') {
            return [
                'verified' => true,
                'duplicate' => false,
                'processed' => false,
                'code' => 'WEBHOOK_MISSING_REQUIRED_FIELD',
            ];
        }

        $order = Order::query()->where('order_code', $orderCode)->first();

        if ($order === null) {
            return [
                'verified' => true,
                'duplicate' => false,
                'processed' => false,
                'code' => 'ORDER_NOT_FOUND',
            ];
        }

        $shouldDispatchFulfillment = false;

        DB::transaction(function () use ($order, $gateway, $gatewayReference, $status, $amount, $method, $payload, $headers, $eventKey, &$shouldDispatchFulfillment): void {
            $previousOrderStatus = (string) $order->status;

            $payment = Payment::query()->firstOrCreate(
                ['gateway_reference' => $gatewayReference],
                [
                    'order_id' => $order->id,
                    'gateway' => $gateway,
                    'method' => is_string($method) ? $method : null,
                    'amount' => $amount,
                    'status' => 'UNPAID',
                    'meta' => [
                        'source' => 'webhook',
                    ],
                ]
            );

            $payment->update([
                'status' => $this->mapPaymentStatus($status),
                'method' => is_string($method) ? $method : $payment->method,
                'amount' => $amount > 0 ? $amount : $payment->amount,
                'paid_at' => in_array($status, ['PAID', 'SUCCESS'], true) ? now() : $payment->paid_at,
                'meta' => array_merge($payment->meta ?? [], [
                    'last_webhook_status' => $status,
                    'last_payload' => $payload,
                ]),
            ]);

            PaymentWebhook::query()->create([
                'payment_id' => $payment->id,
                'gateway' => $gateway,
                'event_key' => $eventKey !== '' ? $eventKey : null,
                'signature' => $this->extractSignature($headers),
                'is_verified' => true,
                'headers' => $headers,
                'payload' => $payload,
                'received_at' => now(),
            ]);

            $orderStatus = match ($status) {
                'PAID', 'SUCCESS' => 'PAID',
                'FAILED' => 'FAILED',
                'EXPIRED' => 'FAILED',
                default => 'PENDING',
            };

            $order->update([
                'status' => $orderStatus,
                'paid_at' => in_array($status, ['PAID', 'SUCCESS'], true) ? now() : $order->paid_at,
                'meta' => array_merge($order->meta ?? [], [
                    'payment_gateway' => $gateway,
                    'payment_reference' => $gatewayReference,
                ]),
            ]);

            $shouldDispatchFulfillment = in_array($orderStatus, ['PAID'], true)
                && !in_array($previousOrderStatus, ['SUCCESS', 'PROCESSING'], true);
        });

        if ($shouldDispatchFulfillment) {
            FulfillPaidOrderJob::dispatch($order->id);
        }

        return [
            'verified' => true,
            'duplicate' => false,
            'processed' => true,
            'code' => 'WEBHOOK_PROCESSED',
        ];
    }

    /**
     * @return array{verified: bool, code: string}
     */
    private function verify(string $gateway, array $payload, array $headers): array
    {
        $providedSignature = $this->extractSignature($headers);
        $timestamp = $this->extractTimestamp($headers);

        if ($providedSignature === null || $providedSignature === '') {
            return [
                'verified' => false,
                'code' => 'WEBHOOK_INVALID_SIGNATURE',
            ];
        }

        if ($timestamp === null) {
            return [
                'verified' => false,
                'code' => 'WEBHOOK_MISSING_TIMESTAMP',
            ];
        }

        if (!$this->isWithinReplayWindow($timestamp)) {
            return [
                'verified' => false,
                'code' => 'WEBHOOK_EXPIRED_TIMESTAMP',
            ];
        }

        $secret = (string) config('services.payment_gateways.'.$gateway.'.webhook_secret', '');

        if ($secret === '') {
            return [
                'verified' => false,
                'code' => 'WEBHOOK_INVALID_SIGNATURE',
            ];
        }

        $raw = json_encode($payload, JSON_UNESCAPED_SLASHES);

        if (!is_string($raw)) {
            return [
                'verified' => false,
                'code' => 'WEBHOOK_INVALID_SIGNATURE',
            ];
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$raw, $secret);

        return [
            'verified' => hash_equals($expected, $providedSignature),
            'code' => hash_equals($expected, $providedSignature)
                ? 'WEBHOOK_SIGNATURE_VERIFIED'
                : 'WEBHOOK_INVALID_SIGNATURE',
        ];
    }

    private function extractSignature(array $headers): ?string
    {
        foreach ($headers as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (Str::lower($key) === 'x-signature') {
                return is_string($value) ? $value : null;
            }
        }

        return null;
    }

    private function extractTimestamp(array $headers): ?int
    {
        foreach ($headers as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (Str::lower($key) !== 'x-timestamp') {
                continue;
            }

            $stringValue = is_string($value) ? trim($value) : '';
            if ($stringValue === '') {
                return null;
            }

            if (ctype_digit($stringValue)) {
                return (int) $stringValue;
            }

            $parsed = strtotime($stringValue);

            return $parsed === false ? null : $parsed;
        }

        return null;
    }

    private function isWithinReplayWindow(int $timestamp): bool
    {
        $allowedDriftSeconds = max(30, (int) config('services.payment_webhook.allowed_drift_seconds', 300));

        return abs(now()->timestamp - $timestamp) <= $allowedDriftSeconds;
    }

    private function mapPaymentStatus(string $status): string
    {
        return match ($status) {
            'PAID', 'SUCCESS' => 'PAID',
            'FAILED' => 'FAILED',
            'EXPIRED' => 'EXPIRED',
            default => 'UNPAID',
        };
    }
}
