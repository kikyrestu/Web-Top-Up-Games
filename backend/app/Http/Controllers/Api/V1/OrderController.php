<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Order\Services\OrderService;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quote_token' => ['required', 'string', 'max:64'],
            'idempotency_key' => ['required', 'string', 'max:64'],
        ]);

        $cacheKey = 'quote_token:'.$validated['quote_token'];
        $quote = Cache::get($cacheKey);

        if (!is_array($quote)) {
            return response()->json([
                'success' => false,
                'code' => 'QUOTE_EXPIRED',
                'message' => 'Quote token is expired or invalid',
                'data' => null,
            ], 422);
        }

        $result = $this->orderService->create([
            'idempotency_key' => $validated['idempotency_key'],
            'quote_token' => $validated['quote_token'],
            'product_id' => $quote['product_id'],
            'product_type' => $quote['product_type'],
            'quantity' => $quote['quantity'],
            'customer_target' => $quote['customer_target'],
            'base_price' => $quote['base_price'],
            'admin_fee' => $quote['admin_fee'],
            'margin' => $quote['margin'],
            'final_amount' => $quote['final_amount'],
            'selected_provider' => $quote['selected_provider'],
            'candidates' => $quote['candidates'],
        ]);

        if (($result['created'] ?? false) === true) {
            Cache::forget($cacheKey);
        }

        /** @var Order $order */
        $order = $result['order'];

        return response()->json([
            'success' => true,
            'code' => ($result['created'] ?? false) ? 'ORDER_CREATED' : 'ORDER_ALREADY_EXISTS',
            'message' => ($result['created'] ?? false)
                ? 'Order created successfully'
                : 'Order already exists for this idempotency key',
            'data' => [
                'order_code' => $order->order_code,
                'status' => $order->status,
                'final_amount' => (float) $order->final_amount,
                'product_id' => (int) $order->product_id,
            ],
        ]);
    }

    public function show(string $orderCode): JsonResponse
    {
        $order = Order::query()
            ->with('items')
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
            'code' => 'ORDER_FOUND',
            'message' => 'Order detail loaded',
            'data' => [
                'order_code' => $order->order_code,
                'status' => $order->status,
                'product_type' => $order->product_type,
                'customer_target' => $order->customer_target,
                'base_price' => (float) $order->base_price,
                'admin_fee' => (float) $order->admin_fee,
                'margin' => (float) $order->margin,
                'final_amount' => (float) $order->final_amount,
                'items' => $order->items,
                'created_at' => $order->created_at,
            ],
        ]);
    }
}
