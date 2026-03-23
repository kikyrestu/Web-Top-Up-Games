<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Order\Exceptions\RetryableFulfillmentException;
use App\Domain\Provider\Services\ProviderRouterService;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

final class FulfillPaidOrderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [15, 60, 180, 600, 1200];
    }

    public function __construct(public int $orderId)
    {
    }

    public function handle(ProviderRouterService $router): void
    {
        $order = Order::query()->find($this->orderId);

        if ($order === null) {
            return;
        }

        if (!in_array($order->status, ['PAID', 'PROCESSING'], true)) {
            return;
        }

        /** @var array<string, mixed> $meta */
        $meta = is_array($order->meta) ? $order->meta : [];

        /** @var array<int, array<string, mixed>> $candidates */
        $candidates = is_array($meta['candidates'] ?? null) ? $meta['candidates'] : [];

        if ($candidates === []) {
            return;
        }

        $order->update([
            'status' => 'PROCESSING',
            'processed_at' => now(),
        ]);

        $result = $router->dispatch($candidates, [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'customer_target' => $order->customer_target,
            'meta' => $meta,
        ]);

        $status = strtoupper((string) ($result['status'] ?? 'PENDING'));
        $isRetryable = (bool) ($result['is_retryable'] ?? false);
        $raw = is_array($result['raw'] ?? null) ? $result['raw'] : [];
        $failureReason = (string) ($raw['error'] ?? $raw['message'] ?? 'provider_dispatch_failed');

        if (in_array($status, ['SUCCESS', 'PAID'], true)) {
            $order->update([
                'status' => 'SUCCESS',
                'completed_at' => now(),
            ]);

            return;
        }

        if (in_array($status, ['FAILED', 'ERROR'], true)) {
            $updatedMeta = array_merge($meta, [
                'fulfillment' => [
                    'last_status' => $status,
                    'last_error' => $failureReason,
                    'retryable' => $isRetryable,
                    'attempt' => $this->attempts(),
                    'updated_at' => now()->toISOString(),
                ],
            ]);

            if ($isRetryable && $this->attempts() < $this->tries) {
                $order->update([
                    'status' => 'PROCESSING',
                    'meta' => $updatedMeta,
                ]);

                throw new RetryableFulfillmentException('Retryable provider dispatch failure: '.$failureReason);
            }

            $order->update([
                'status' => 'FAILED',
                'meta' => array_merge($updatedMeta, [
                    'fulfillment' => array_merge(
                        is_array($updatedMeta['fulfillment'] ?? null) ? $updatedMeta['fulfillment'] : [],
                        [
                            'dead_lettered' => true,
                            'dead_lettered_at' => now()->toISOString(),
                        ]
                    ),
                ]),
            ]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $order = Order::query()->find($this->orderId);

        if ($order === null || $order->status === 'SUCCESS') {
            return;
        }

        $meta = is_array($order->meta) ? $order->meta : [];
        $message = $exception?->getMessage() ?? 'fulfillment_job_failed';

        $order->update([
            'status' => 'FAILED',
            'meta' => array_merge($meta, [
                'fulfillment' => [
                    'dead_lettered' => true,
                    'dead_lettered_at' => now()->toISOString(),
                    'dead_letter_message' => $message,
                    'attempt' => $this->attempts(),
                ],
            ]),
        ]);
    }
}
