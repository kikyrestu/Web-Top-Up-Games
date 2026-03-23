<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Provider\Services\ProviderRouterService;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderProviderAttempt;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class ProviderRouterServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_retries_same_provider_before_returning_success(): void
    {
        config()->set('services.provider_router.max_retries_per_provider', 1);
        config()->set('services.digiflazz.base_url', 'https://digiflazz.test/v1');
        config()->set('services.digiflazz.username', 'digiflazz-user');
        config()->set('services.digiflazz.api_key', 'digiflazz-key');
        config()->set('services.rajabiller.base_url', 'https://rajabiller.test');
        config()->set('services.rajabiller.username', 'rajabiller-user');
        config()->set('services.rajabiller.api_key', 'rajabiller-key');

        $digiflazz = Provider::query()->create([
            'code' => 'DIGIFLAZZ',
            'name' => 'Digiflazz',
            'is_active' => true,
        ]);

        $rajabiller = Provider::query()->create([
            'code' => 'RAJABILLER',
            'name' => 'Rajabiller',
            'is_active' => true,
        ]);

        $order = $this->createOrder();

        Http::fake([
            'https://digiflazz.test/*' => Http::sequence()
                ->push(['error' => 'temporary overload'], 503)
                ->push([
                    'data' => [
                        'status' => 'SUKSES',
                        'ref_id' => 'DF-REF-001',
                    ],
                ], 200),
            'https://rajabiller.test/*' => Http::response([
                'data' => [
                    'status' => 'SUCCESS',
                    'ref_id' => 'RB-REF-001',
                ],
            ], 200),
        ]);

        $result = app(ProviderRouterService::class)->dispatch([
            [
                'provider_id' => $digiflazz->id,
                'provider_code' => 'DIGIFLAZZ',
                'provider_product_code' => 'SKU-ML-86',
            ],
            [
                'provider_id' => $rajabiller->id,
                'provider_code' => 'RAJABILLER',
                'provider_product_code' => 'SKU-ML-86-RB',
            ],
        ], [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'customer_target' => $order->customer_target,
        ]);

        $this->assertSame('SUCCESS', $result['status']);

        $attempts = OrderProviderAttempt::query()->orderBy('attempt_no')->get();

        $this->assertCount(2, $attempts);
        $this->assertSame($digiflazz->id, $attempts[0]->provider_id);
        $this->assertSame('FAILED', $attempts[0]->status);
        $this->assertSame($digiflazz->id, $attempts[1]->provider_id);
        $this->assertSame('SUCCESS', $attempts[1]->status);
        $this->assertSame(0, OrderProviderAttempt::query()->where('provider_id', $rajabiller->id)->count());

        Http::assertSentCount(2);
        Http::assertSent(function (Request $request): bool {
            return str_contains($request->url(), 'digiflazz.test');
        });
    }

    public function test_it_fails_over_when_retryable_errors_are_exhausted(): void
    {
        config()->set('services.provider_router.max_retries_per_provider', 1);
        config()->set('services.digiflazz.base_url', 'https://digiflazz.test/v1');
        config()->set('services.digiflazz.username', 'digiflazz-user');
        config()->set('services.digiflazz.api_key', 'digiflazz-key');
        config()->set('services.rajabiller.base_url', 'https://rajabiller.test');
        config()->set('services.rajabiller.username', 'rajabiller-user');
        config()->set('services.rajabiller.api_key', 'rajabiller-key');

        $digiflazz = Provider::query()->create([
            'code' => 'DIGIFLAZZ',
            'name' => 'Digiflazz',
            'is_active' => true,
        ]);

        $rajabiller = Provider::query()->create([
            'code' => 'RAJABILLER',
            'name' => 'Rajabiller',
            'is_active' => true,
        ]);

        $order = $this->createOrder();

        Http::fake([
            'https://digiflazz.test/*' => Http::sequence()
                ->push(['error' => 'service unavailable'], 503)
                ->push(['error' => 'still unavailable'], 503),
            'https://rajabiller.test/*' => Http::response([
                'data' => [
                    'status' => 'PROCESSING',
                    'ref_id' => 'RB-REF-009',
                ],
            ], 200),
        ]);

        $result = app(ProviderRouterService::class)->dispatch([
            [
                'provider_id' => $digiflazz->id,
                'provider_code' => 'DIGIFLAZZ',
                'provider_product_code' => 'SKU-ML-86',
            ],
            [
                'provider_id' => $rajabiller->id,
                'provider_code' => 'RAJABILLER',
                'provider_product_code' => 'SKU-ML-86-RB',
            ],
        ], [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'customer_target' => $order->customer_target,
        ]);

        $this->assertSame('PENDING', $result['status']);

        $attempts = OrderProviderAttempt::query()->orderBy('attempt_no')->get();

        $this->assertCount(3, $attempts);
        $this->assertSame($digiflazz->id, $attempts[0]->provider_id);
        $this->assertSame('FAILED', $attempts[0]->status);
        $this->assertSame($digiflazz->id, $attempts[1]->provider_id);
        $this->assertSame('FAILED', $attempts[1]->status);
        $this->assertSame($rajabiller->id, $attempts[2]->provider_id);
        $this->assertSame('PENDING', $attempts[2]->status);
    }

    private function createOrder(): Order
    {
        $category = Category::query()->create([
            'name' => 'Top Up Game',
            'slug' => 'topup-provider-router',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'ML 86 Diamond',
            'slug' => 'ml-86-provider-router',
            'sku' => 'ML-86-PROVIDER-ROUTER',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        return Order::query()->create([
            'order_code' => 'ORD-PROVIDER-ROUTER-001',
            'product_id' => $product->id,
            'product_type' => 'TOPUP',
            'customer_target' => '08123456789',
            'base_price' => 20000,
            'admin_fee' => 0,
            'margin' => 1500,
            'final_amount' => 21500,
            'status' => 'PAID',
        ]);
    }
}
