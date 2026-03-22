<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_webhook_updates_payment_and_order_status(): void
    {
        config()->set('services.payment_gateways.MIDTRANS.webhook_secret', 'test-secret-midtrans');

        $order = $this->createOrder();

        $payload = [
            'event_key' => 'evt-midtrans-001',
            'order_code' => $order->order_code,
            'gateway_reference' => 'MID-REF-0001',
            'status' => 'PAID',
            'amount' => 31500,
            'method' => 'qris',
        ];

        $signature = hash_hmac('sha256', json_encode(array_merge($payload, ['gateway' => 'MIDTRANS']), JSON_UNESCAPED_SLASHES), 'test-secret-midtrans');

        $response = $this
            ->withHeader('x-signature', $signature)
            ->postJson('/api/v1/payments/webhook/midtrans', $payload);

        $response
            ->assertOk()
            ->assertJsonPath('code', 'WEBHOOK_PROCESSED')
            ->assertJsonPath('data.verified', true)
            ->assertJsonPath('data.processed', true);

        $payment = Payment::query()->where('gateway_reference', 'MID-REF-0001')->first();

        $this->assertNotNull($payment);
        $this->assertSame('PAID', $payment->status);

        $order->refresh();
        $this->assertSame('PAID', $order->status);
    }

    public function test_duplicate_webhook_event_key_is_deduplicated(): void
    {
        config()->set('services.payment_gateways.DUITKU.webhook_secret', 'test-secret-duitku');

        $order = $this->createOrder();

        $payload = [
            'event_key' => 'evt-duitku-001',
            'order_code' => $order->order_code,
            'gateway_reference' => 'DUITKU-REF-1',
            'status' => 'PAID',
            'amount' => 31500,
            'method' => 'va',
        ];

        $fullPayload = array_merge($payload, ['gateway' => 'DUITKU']);
        $signature = hash_hmac('sha256', json_encode($fullPayload, JSON_UNESCAPED_SLASHES), 'test-secret-duitku');

        $this
            ->withHeader('x-signature', $signature)
            ->postJson('/api/v1/payments/webhook/duitku', $payload)
            ->assertOk()
            ->assertJsonPath('code', 'WEBHOOK_PROCESSED');

        $this
            ->withHeader('x-signature', $signature)
            ->postJson('/api/v1/payments/webhook/duitku', $payload)
            ->assertOk()
            ->assertJsonPath('code', 'WEBHOOK_DUPLICATE_EVENT')
            ->assertJsonPath('data.duplicate', true);
    }

    private function createOrder(): Order
    {
        $category = Category::query()->create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-game-webhook',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Mobile Legends 86',
            'slug' => 'ml-86-webhook',
            'sku' => 'ML-86-WEBHOOK',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        return Order::query()->create([
            'order_code' => 'ORD-WEBHOOK-001',
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
