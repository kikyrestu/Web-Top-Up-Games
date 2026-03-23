<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

final class OrderProviderAttemptController extends Controller
{
    public function index(string $orderCode): JsonResponse
    {
        $order = Order::query()
            ->with(['providerAttempts.provider'])
            ->where('order_code', $orderCode)
            ->first();

        if ($order === null) {
            return response()->json([
                'success' => false,
                'code' => 'ORDER_NOT_FOUND',
                'message' => 'Order not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'code' => 'ORDER_PROVIDER_ATTEMPTS_FOUND',
            'message' => 'Provider attempts loaded',
            'data' => [
                'order_code' => $order->order_code,
                'order_status' => $order->status,
                'attempts' => $order->providerAttempts
                    ->sortBy('attempt_no')
                    ->values()
                    ->map(static fn ($attempt) => [
                        'attempt_no' => (int) $attempt->attempt_no,
                        'provider' => [
                            'id' => (int) $attempt->provider_id,
                            'code' => $attempt->provider?->code,
                            'name' => $attempt->provider?->name,
                        ],
                        'status' => (string) $attempt->status,
                        'provider_ref' => $attempt->provider_ref,
                        'request_payload' => $attempt->request_payload,
                        'response_payload' => $attempt->response_payload,
                        'attempted_at' => $attempt->attempted_at,
                    ]),
            ],
        ]);
    }
}
