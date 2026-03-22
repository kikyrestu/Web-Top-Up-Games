<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Provider\Services\ProviderRouterService;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class FulfillPaidOrderJob implements ShouldQueue
{
    use Queueable;

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

        if (in_array($status, ['SUCCESS', 'PAID'], true)) {
            $order->update([
                'status' => 'SUCCESS',
                'completed_at' => now(),
            ]);

            return;
        }

        if (in_array($status, ['FAILED', 'ERROR'], true)) {
            $order->update([
                'status' => 'FAILED',
            ]);
        }
    }
}
