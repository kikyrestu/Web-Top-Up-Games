<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Payment\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_code' => ['required', 'string', 'max:50'],
            'gateway' => ['required', 'string', 'max:40'],
            'method' => ['nullable', 'string', 'max:50'],
        ]);

        $order = Order::query()
            ->where('order_code', $validated['order_code'])
            ->first();

        if ($order === null) {
            return response()->json([
                'success' => false,
                'code' => 'ORDER_NOT_FOUND',
                'message' => 'Order not found',
                'data' => null,
            ], 404);
        }

        $payment = $this->paymentService->initiate(
            $order,
            $validated['gateway'],
            $validated['method'] ?? null
        );

        return response()->json([
            'success' => true,
            'code' => 'PAYMENT_INITIATED',
            'message' => 'Payment reference generated',
            'data' => [
                'order_code' => $order->order_code,
                'gateway' => $payment->gateway,
                'gateway_reference' => $payment->gateway_reference,
                'status' => $payment->status,
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
            ],
        ]);
    }

    public function status(string $gatewayReference): JsonResponse
    {
        $payment = Payment::query()
            ->with('order')
            ->where('gateway_reference', $gatewayReference)
            ->first();

        if ($payment === null) {
            return response()->json([
                'success' => false,
                'code' => 'PAYMENT_NOT_FOUND',
                'message' => 'Payment reference not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'code' => 'PAYMENT_STATUS_FOUND',
            'message' => 'Payment status loaded',
            'data' => [
                'gateway_reference' => $payment->gateway_reference,
                'gateway' => $payment->gateway,
                'status' => $payment->status,
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'order' => [
                    'order_code' => $payment->order?->order_code,
                    'status' => $payment->order?->status,
                ],
                'paid_at' => $payment->paid_at,
            ],
        ]);
    }
}
