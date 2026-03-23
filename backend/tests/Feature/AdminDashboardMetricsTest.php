<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\AuditLog;
use App\Models\IdempotencyRequest;
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

        AuditLog::query()->create([
            'event_type' => 'IDEMPOTENCY_PURGE_COMPLETED',
            'actor_type' => 'SYSTEM',
            'actor_id' => null,
            'entity_type' => 'IDEMPOTENCY',
            'entity_id' => null,
            'payload' => [
                'deleted_records' => 12,
            ],
            'occurred_at' => now()->subMinutes(5),
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
            ->assertJsonPath('data.payments.0.paid_rate_pct', 50)
            ->assertJsonPath('data.housekeeping.idempotency_purge.runs', 1)
            ->assertJsonPath('data.housekeeping.idempotency_purge.total_deleted', 12)
            ->assertJsonPath('data.housekeeping.idempotency_purge.last_deleted', 12);

        $this->assertNotNull($response->json('data.providers.0.p95_latency_ms'));

        $alertResponse = $this->getJson('/api/v1/admin/dashboard/metrics?hours=24&alert_min_attempts=1&alert_success_rate_threshold=85');

        $alertResponse
            ->assertOk()
            ->assertJsonPath('code', 'ADMIN_DASHBOARD_METRICS')
            ->assertJsonPath('data.alerts.summary.has_alerts', true)
            ->assertJsonPath('data.alerts.providers.0.provider_code', 'DIGIFLAZZ')
            ->assertJsonPath('data.alerts.providers.0.success_rate_pct', 50)
            ->assertJsonPath('data.alerts.providers.0.threshold_pct', 85)
            ->assertJsonPath('data.alerts.providers.0.severity', 'HIGH');

        $paymentAlertResponse = $this->getJson('/api/v1/admin/dashboard/metrics?hours=24&payment_alert_min_total=1&payment_alert_paid_rate_threshold=80');

        $paymentAlertResponse
            ->assertOk()
            ->assertJsonPath('code', 'ADMIN_DASHBOARD_METRICS')
            ->assertJsonPath('data.alerts.payments.0.gateway', 'MIDTRANS')
            ->assertJsonPath('data.alerts.payments.0.paid_rate_pct', 50)
            ->assertJsonPath('data.alerts.payments.0.threshold_pct', 80)
            ->assertJsonPath('data.alerts.payments.0.severity', 'HIGH');

        $alertsOnlyResponse = $this->getJson('/api/v1/admin/dashboard/alerts?hours=24&alert_min_attempts=1&alert_success_rate_threshold=85&payment_alert_min_total=1&payment_alert_paid_rate_threshold=80');

        $alertsOnlyResponse
            ->assertOk()
            ->assertJsonPath('code', 'ADMIN_DASHBOARD_ALERTS')
            ->assertJsonPath('data.alerts.summary.has_alerts', true)
            ->assertJsonPath('data.alerts.providers.0.provider_code', 'DIGIFLAZZ')
            ->assertJsonPath('data.alerts.payments.0.gateway', 'MIDTRANS');

        IdempotencyRequest::query()->create([
            'scope' => 'POST:api/v1/payments/initiate',
            'idempotency_key' => 'expired-dashboard-1',
            'actor_fingerprint' => 'guest:dashboard',
            'request_hash' => hash('sha256', 'expired-dashboard-1'),
            'response_status' => 200,
            'response_body' => ['ok' => true],
            'expires_at' => now()->subMinutes(30),
        ]);

        IdempotencyRequest::query()->create([
            'scope' => 'POST:api/v1/payments/initiate',
            'idempotency_key' => 'active-dashboard-1',
            'actor_fingerprint' => 'guest:dashboard-2',
            'request_hash' => hash('sha256', 'active-dashboard-1'),
            'response_status' => 200,
            'response_body' => ['ok' => true],
            'expires_at' => now()->addMinutes(30),
        ]);

        $housekeepingResponse = $this->getJson('/api/v1/admin/dashboard/housekeeping?hours=24');

        $housekeepingResponse
            ->assertOk()
            ->assertJsonPath('code', 'ADMIN_DASHBOARD_HOUSEKEEPING')
            ->assertJsonPath('data.idempotency.total_records', 2)
            ->assertJsonPath('data.idempotency.expired_records', 1)
            ->assertJsonPath('data.idempotency.purge_runs', 1)
            ->assertJsonPath('data.idempotency.purge_total_deleted', 12)
            ->assertJsonPath('data.idempotency.purge_last_deleted', 12);

        AuditLog::query()->create([
            'event_type' => 'IDEMPOTENCY_PURGE_COMPLETED',
            'actor_type' => 'SYSTEM',
            'actor_id' => null,
            'entity_type' => 'IDEMPOTENCY',
            'entity_id' => null,
            'payload' => [
                'deleted_records' => 7,
            ],
            'occurred_at' => now()->subMinutes(1),
        ]);

        $historyResponse = $this->getJson('/api/v1/admin/dashboard/housekeeping/history?hours=24&limit=1');

        $historyResponse
            ->assertOk()
            ->assertJsonPath('code', 'ADMIN_DASHBOARD_HOUSEKEEPING_HISTORY')
            ->assertJsonPath('data.limit', 1)
            ->assertJsonPath('data.count', 1)
            ->assertJsonPath('data.runs.0.deleted_records', 7);

        $excelResponse = $this->get('/api/v1/admin/dashboard/metrics/excel?hours=24&alert_min_attempts=1&alert_success_rate_threshold=85');

        $excelResponse->assertOk();
        $this->assertStringContainsString('application/vnd.ms-excel', (string) $excelResponse->headers->get('content-type'));
        $this->assertStringContainsString('.xls', (string) $excelResponse->headers->get('content-disposition'));
    }

    public function test_non_admin_is_forbidden_from_dashboard_metrics(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/dashboard/metrics')
            ->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN');

        $this->getJson('/api/v1/admin/dashboard/alerts')
            ->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN');

        $this->getJson('/api/v1/admin/dashboard/housekeeping')
            ->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN');

        $this->getJson('/api/v1/admin/dashboard/housekeeping/history')
            ->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN');

        $this->get('/api/v1/admin/dashboard/metrics/excel')
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
