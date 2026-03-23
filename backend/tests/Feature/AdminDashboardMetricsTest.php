<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProviderAttempt;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ProviderHealthCheck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_dashboard_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

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
            'status' => 'SUCCESS',
            'provider_ref' => 'DF-REF-100',
            'request_payload' => ['buyer_sku_code' => 'SKU-ML86'],
            'response_payload' => ['raw' => ['status' => 'success']],
            'attempted_at' => now()->subMinutes(30),
        ]);

        OrderProviderAttempt::query()->create([
            'order_id' => $order->id,
            'provider_id' => $provider->id,
            'attempt_no' => 2,
            'status' => 'FAILED',
            'provider_ref' => null,
            'request_payload' => ['buyer_sku_code' => 'SKU-ML86'],
            'response_payload' => ['raw' => ['error' => 'timeout']],
            'attempted_at' => now()->subMinutes(25),
        ]);

        ProviderHealthCheck::query()->create([
            'provider_id' => $provider->id,
            'status' => 'SUCCESS',
            'response_time_ms' => 120,
            'checked_at' => now()->subMinutes(20),
        ]);

        ProviderHealthCheck::query()->create([
            'provider_id' => $provider->id,
            'status' => 'SUCCESS',
            'response_time_ms' => 240,
            'checked_at' => now()->subMinutes(10),
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'MIDTRANS',
            'gateway_reference' => 'MID-METRIC-001',
            'method' => 'qris',
            'amount' => 31500,
            'status' => 'PAID',
            'paid_at' => now(),
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'MIDTRANS',
            'gateway_reference' => 'MID-METRIC-002',
            'method' => 'qris',
            'amount' => 31500,
            'status' => 'FAILED',
        ]);

        $response = $this->getJson('/api/v1/admin/dashboard/metrics?hours=24');

        $response
            ->assertOk()
            ->assertJsonPath('code', 'ADMIN_DASHBOARD_METRICS')
            ->assertJsonPath('data.window_hours', 24)
            ->assertJsonPath('data.providers.0.provider_code', 'DIGIFLAZZ')
            ->assertJsonPath('data.providers.0.attempts', 2)
            ->assertJsonPath('data.providers.0.success_rate_pct', 50)
            ->assertJsonPath('data.providers.0.top_fail_reasons.0.reason', 'timeout')
            ->assertJsonPath('data.payments.0.gateway', 'MIDTRANS')
            ->assertJsonPath('data.payments.0.total', 2)
            ->assertJsonPath('data.payments.0.paid_rate_pct', 50);

        $this->assertNotNull($response->json('data.providers.0.p95_latency_ms'));
    }

    public function test_non_admin_is_forbidden_from_dashboard_metrics(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/dashboard/metrics')
            ->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN');
    }

    private function createOrder(): Order
    {
        $category = Category::query()->create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-dashboard-metrics',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Mobile Legends 86',
            'slug' => 'ml-86-dashboard-metrics',
            'sku' => 'ML-86-DASHBOARD-METRICS',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        return Order::query()->create([
            'order_code' => 'ORD-DASHBOARD-METRICS-001',
            'product_id' => $product->id,
            'product_type' => 'TOPUP',
            'customer_target' => '123123123',
            'base_price' => 30000,
            'admin_fee' => 0,
            'margin' => 1500,
            'final_amount' => 31500,
            'status' => 'PROCESSING',
        ]);
    }
}
