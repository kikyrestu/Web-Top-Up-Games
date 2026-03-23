<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderReprocessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_queue_reprocess_for_failed_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $order = $this->createOrder('FAILED');

        $response = $this->postJson('/api/v1/admin/orders/'.$order->order_code.'/reprocess');

        $response
            ->assertOk()
            ->assertJsonPath('code', 'ORDER_REPROCESS_QUEUED');

        $order->refresh();
        $this->assertSame('PAID', $order->status);
    }

    public function test_non_admin_cannot_access_reprocess_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $order = $this->createOrder('FAILED');

        $this->postJson('/api/v1/admin/orders/'.$order->order_code.'/reprocess')
            ->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN');
    }

    private function createOrder(string $status): Order
    {
        $category = Category::query()->create([
            'name' => 'Top Up Game',
            'slug' => 'topup-admin-reprocess',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'ML 86 Diamond',
            'slug' => 'ml-86-admin-reprocess',
            'sku' => 'ML-86-ADMIN-REPROCESS',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        return Order::query()->create([
            'order_code' => 'ORD-ADMIN-REPROCESS-'.strtoupper(substr($status, 0, 3)),
            'product_id' => $product->id,
            'product_type' => 'TOPUP',
            'customer_target' => '08123456789',
            'base_price' => 20000,
            'admin_fee' => 0,
            'margin' => 1500,
            'final_amount' => 21500,
            'status' => $status,
        ]);
    }
}
