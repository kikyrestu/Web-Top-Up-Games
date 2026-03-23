<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

final class PaymentService
{
    public function __construct(private readonly PaymentGatewayInvoiceService $gatewayInvoiceService)
    {
    }

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
            $existingMeta = is_array($existing->meta) ? $existing->meta : [];

            if (is_string($existingMeta['pay_url'] ?? null) && $existingMeta['pay_url'] !== '') {
                return $existing;
            }

            $invoice = $this->gatewayInvoiceService->createInvoice(
                $order,
                $normalizedGateway,
                (string) $existing->gateway_reference,
                (float) $existing->amount,
                $method ?? $existing->method
            );

            $existing->update([
                'method' => $method ?? $existing->method,
                'expired_at' => $invoice['expired_at'],
                'meta' => array_merge($existingMeta, [
                    'pay_url' => $invoice['pay_url'],
                    'provider_reference' => $invoice['provider_reference'],
                    'invoice_payload' => $invoice['raw'],
                    'initiated_at' => now()->toISOString(),
                ]),
            ]);

            return $existing->refresh();
        }

        $gatewayReference = $this->generateReference($normalizedGateway);
        $invoice = $this->gatewayInvoiceService->createInvoice(
            $order,
            $normalizedGateway,
            $gatewayReference,
            (float) $order->final_amount,
            $method
        );

        return Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => $normalizedGateway,
            'gateway_reference' => $gatewayReference,
            'method' => $method,
            'amount' => (float) $order->final_amount,
            'status' => 'UNPAID',
            'expired_at' => $invoice['expired_at'],
            'meta' => [
                'initiated_at' => now()->toISOString(),
                'pay_url' => $invoice['pay_url'],
                'provider_reference' => $invoice['provider_reference'],
                'invoice_payload' => $invoice['raw'],
            ],
        ]);
    }

    private function generateReference(string $gateway): string
    {
        return $gateway.'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(8));
    }
}
