<?php

declare(strict_types=1);

namespace App\Domain\Order\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class OrderService
{
    /**
     * Create order in PENDING state and return canonical order data.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $idempotencyKey = (string) ($payload['idempotency_key'] ?? '');

        if ($idempotencyKey !== '') {
            $existing = Order::query()
                ->where('meta->idempotency_key', $idempotencyKey)
                ->first();

            if ($existing !== null) {
                return [
                    'created' => false,
                    'order' => $existing,
                ];
            }
        }

        /** @var array{created: bool, order: Order} $result */
        $result = DB::transaction(static function () use ($payload, $idempotencyKey): array {
            $quantity = (int) ($payload['quantity'] ?? 1);
            $basePrice = (float) ($payload['base_price'] ?? 0);
            $adminFee = (float) ($payload['admin_fee'] ?? 0);
            $margin = (float) ($payload['margin'] ?? 0);
            $finalAmount = (float) ($payload['final_amount'] ?? 0);

            $order = Order::query()->create([
                'order_code' => 'ORD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6)),
                'user_id' => $payload['user_id'] ?? null,
                'guest_session_id' => $payload['guest_session_id'] ?? null,
                'product_id' => $payload['product_id'],
                'product_type' => $payload['product_type'],
                'customer_target' => $payload['customer_target'] ?? null,
                'base_price' => $basePrice,
                'admin_fee' => $adminFee,
                'margin' => $margin,
                'final_amount' => $finalAmount,
                'status' => 'PENDING',
                'meta' => [
                    'idempotency_key' => $idempotencyKey,
                    'quote_token' => $payload['quote_token'] ?? null,
                    'selected_provider' => $payload['selected_provider'] ?? null,
                    'candidates' => $payload['candidates'] ?? [],
                ],
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $payload['product_id'],
                'quantity' => $quantity,
                'unit_price' => $basePrice + $adminFee + $margin,
                'subtotal' => $finalAmount,
            ]);

            return [
                'created' => true,
                'order' => $order->fresh('items'),
            ];
        });

        return $result;
    }
}
