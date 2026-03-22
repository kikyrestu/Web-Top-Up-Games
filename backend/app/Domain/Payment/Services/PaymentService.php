<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

final class PaymentService
{
    /**
     * Create or return unpaid payment reference for an order.
     */
    public function initiate(Order $order, string $gateway, ?string $method = null): Payment
    {
        $normalizedGateway = strtoupper($gateway);

        $existing = Payment::query()
            ->where('order_id', $order->id)
            ->where('gateway', $normalizedGateway)
            ->whereIn('status', ['UNPAID', 'PAID'])
            ->latest('id')
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => $normalizedGateway,
            'gateway_reference' => $this->generateReference($normalizedGateway),
            'method' => $method,
            'amount' => (float) $order->final_amount,
            'status' => 'UNPAID',
            'meta' => [
                'initiated_at' => now()->toISOString(),
            ],
        ]);
    }

    private function generateReference(string $gateway): string
    {
        return $gateway.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8));
    }
}
