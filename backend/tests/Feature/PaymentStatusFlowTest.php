<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentStatusFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_payment_status_by_gateway_reference(): void
    {
        $order = $this->createOrder();

        Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'MIDTRANS',
            'gateway_reference' => 'MID-STATUS-001',
            'method' => 'qris',
            'amount' => 31500,
            'status' => 'PAID',
            'paid_at' => now(),
            'expired_at' => now()->addMinutes(15),
            'meta' => [
                'pay_url' => 'https://pay.example/mid-status-001',
            ],
        ]);

        $response = $this->getJson('/api/v1/payments/MID-STATUS-001/status');

        $response
            ->assertOk()
            ->assertJsonPath('code', 'PAYMENT_STATUS_FOUND')
            ->assertJsonPath('data.gateway_reference', 'MID-STATUS-001')
            ->assertJsonPath('data.status', 'PAID')
            ->assertJsonPath('data.pay_url', 'https://pay.example/mid-status-001')
            ->assertJsonPath('data.order.order_code', $order->order_code);

        $this->assertNotNull($response->json('data.expired_at'));
    }

    private function createOrder(): Order
    {
        $category = Category::query()->create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-game-payment-status',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Mobile Legends 86',
            'slug' => 'ml-86-payment-status',
            'sku' => 'ML-86-PAYMENT-STATUS',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        return Order::query()->create([
            'order_code' => 'ORD-PAYMENT-STATUS-001',
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
