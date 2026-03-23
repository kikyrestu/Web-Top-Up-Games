<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProviderAttempt;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderProviderAttemptsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_provider_attempts_for_an_order(): void
    {
        $provider = Provider::query()->create([
            'code' => 'DIGIFLAZZ',
            'name' => 'Digiflazz',
            'is_active' => true,
        ]);

        $order = $this->createOrder();

        OrderProviderAttempt::query()->create([
            'order_id' => $order->id,
            'provider_id' => $provider->id,
            'attempt_no' => 1,
            'status' => 'FAILED',
            'provider_ref' => null,
            'request_payload' => ['buyer_sku_code' => 'SKU-DIGIFLAZZ-ML86'],
            'response_payload' => ['error' => 'timeout'],
            'attempted_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/admin/orders/'.$order->order_code.'/provider-attempts');

        $response
            ->assertOk()
            ->assertJsonPath('code', 'ORDER_PROVIDER_ATTEMPTS_FOUND')
            ->assertJsonPath('data.order_code', $order->order_code)
            ->assertJsonPath('data.attempts.0.provider.code', 'DIGIFLAZZ')
            ->assertJsonPath('data.attempts.0.status', 'FAILED');
    }

    private function createOrder(): Order
    {
        $category = Category::query()->create([
            'name' => 'Top Up Game',
            'slug' => 'topup-admin-attempts',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'ML 86 Diamond',
            'slug' => 'ml-86-admin-attempts',
            'sku' => 'ML-86-ADMIN-ATTEMPTS',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        return Order::query()->create([
            'order_code' => 'ORD-ADMIN-ATTEMPTS-001',
            'product_id' => $product->id,
            'product_type' => 'TOPUP',
            'customer_target' => '08123456789',
            'base_price' => 20000,
            'admin_fee' => 0,
            'margin' => 1500,
            'final_amount' => 21500,
            'status' => 'PROCESSING',
        ]);
    }
}
