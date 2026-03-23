<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AccountTransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:30'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $status = strtoupper(trim((string) ($validated['status'] ?? '')));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $orders = Order::query()
            ->with(['product:id,name,slug,type', 'payment:id,order_id,status,gateway,gateway_reference,amount,paid_at,expired_at'])
            ->where('user_id', (int) $request->user()->id)
            ->when($status !== '', static fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'code' => 'ACCOUNT_TRANSACTIONS_FOUND',
            'message' => 'Account transactions loaded',
            'data' => [
                'items' => $orders->getCollection()->map(static fn (Order $order): array => [
                    'order_code' => (string) $order->order_code,
                    'status' => (string) $order->status,
                    'product' => [
                        'id' => (int) ($order->product?->id ?? 0),
                        'name' => (string) ($order->product?->name ?? ''),
                        'slug' => (string) ($order->product?->slug ?? ''),
                        'type' => (string) ($order->product?->type ?? ''),
                    ],
                    'amount' => (float) $order->final_amount,
                    'payment' => [
                        'status' => (string) ($order->payment?->status ?? ''),
                        'gateway' => (string) ($order->payment?->gateway ?? ''),
                        'gateway_reference' => (string) ($order->payment?->gateway_reference ?? ''),
                        'amount' => $order->payment?->amount !== null ? (float) $order->payment->amount : null,
                        'paid_at' => $order->payment?->paid_at?->toISOString(),
                        'expired_at' => $order->payment?->expired_at?->toISOString(),
                    ],
                    'created_at' => $order->created_at?->toISOString(),
                    'completed_at' => $order->completed_at?->toISOString(),
                ])->values(),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'last_page' => $orders->lastPage(),
                    'total' => $orders->total(),
                ],
            ],
        ]);
    }
}
