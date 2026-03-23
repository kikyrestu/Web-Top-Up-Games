<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentInitiateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_initiates_payment_for_an_order(): void
    {
        $order = $this->createOrder();

        $response = $this->postJson('/api/v1/payments/initiate', [
            'order_code' => $order->order_code,
            'gateway' => 'midtrans',
            'method' => 'qris',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 'PAYMENT_INITIATED')
            ->assertJsonPath('data.order_code', $order->order_code)
            ->assertJsonPath('data.gateway', 'MIDTRANS')
            ->assertJsonPath('data.status', 'UNPAID');

        $payUrl = (string) $response->json('data.pay_url');
        $this->assertStringEndsWith('/pay/midtrans/'.$response->json('data.gateway_reference'), $payUrl);

        $this->assertNotNull($response->json('data.expired_at'));
    }

    public function test_it_returns_same_response_for_replayed_idempotency_key(): void
    {
        $order = $this->createOrder();

        $payload = [
            'order_code' => $order->order_code,
            'gateway' => 'midtrans',
            'method' => 'qris',
        ];

        $first = $this
            ->withHeader('x-idempotency-key', 'idem-payment-001')
            ->postJson('/api/v1/payments/initiate', $payload)
            ->assertOk();

        $second = $this
            ->withHeader('x-idempotency-key', 'idem-payment-001')
            ->postJson('/api/v1/payments/initiate', $payload)
            ->assertOk();

        $this->assertSame(
            $first->json('data.gateway_reference'),
            $second->json('data.gateway_reference')
        );
    }

    public function test_it_rejects_reused_idempotency_key_for_different_payload(): void
    {
        $order = $this->createOrder();

        $this
            ->withHeader('x-idempotency-key', 'idem-payment-002')
            ->postJson('/api/v1/payments/initiate', [
                'order_code' => $order->order_code,
                'gateway' => 'midtrans',
                'method' => 'qris',
            ])
            ->assertOk();

        $this
            ->withHeader('x-idempotency-key', 'idem-payment-002')
            ->postJson('/api/v1/payments/initiate', [
                'order_code' => $order->order_code,
                'gateway' => 'duitku',
                'method' => 'va',
            ])
            ->assertStatus(409)
            ->assertJsonPath('code', 'IDEMPOTENCY_KEY_REUSED_WITH_DIFFERENT_PAYLOAD');
    }

    private function createOrder(): Order
    {
        $category = Category::query()->create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-game-payment-init',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Mobile Legends 86',
            'slug' => 'ml-86-payment-init',
            'sku' => 'ML-86-PAYMENT-INIT',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        return Order::query()->create([
            'order_code' => 'ORD-PAYMENT-INIT-001',
            'product_id' => $product->id,
            'product_type' => 'TOPUP',
            'customer_target' => '123123123',
            'base_price' => 30000,
            'admin_fee' => 0,
            'margin' => 1500,
            'final_amount' => 31500,
            'status' => 'PENDING',
        ]);
    }
}
