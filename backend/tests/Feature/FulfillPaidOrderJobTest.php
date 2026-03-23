<?php

namespace Tests\Feature;

use App\Domain\Order\Exceptions\RetryableFulfillmentException;
use App\Domain\Provider\Services\ProviderRouterService;
use App\Jobs\FulfillPaidOrderJob;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FulfillPaidOrderJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_throws_retryable_exception_for_retryable_provider_failure(): void
    {
        config()->set('services.provider_router.max_retries_per_provider', 0);
        config()->set('services.provider_router.circuit_breaker_enabled', false);
        config()->set('services.digiflazz.base_url', 'https://digiflazz.test/v1');
        config()->set('services.digiflazz.username', 'digiflazz-user');
        config()->set('services.digiflazz.api_key', 'digiflazz-key');

        Http::fake([
            'https://digiflazz.test/*' => Http::response(['error' => 'provider timeout'], 503),
        ]);

        $order = $this->createPaidOrder();

        $router = app(ProviderRouterService::class);

        $job = new FulfillPaidOrderJob($order->id);

        try {
            $job->handle($router);
            $this->fail('Expected RetryableFulfillmentException was not thrown.');
        } catch (RetryableFulfillmentException) {
            $order->refresh();
            $this->assertSame('PROCESSING', $order->status);
            $this->assertTrue((bool) data_get($order->meta, 'fulfillment.retryable'));
            $this->assertSame('http_status_503', (string) data_get($order->meta, 'fulfillment.last_error'));
        }
    }

    public function test_failed_hook_marks_order_as_dead_lettered(): void
    {
        $order = $this->createPaidOrder();

        $job = new FulfillPaidOrderJob($order->id);
        $job->failed(new \RuntimeException('max retries reached'));

        $order->refresh();

        $this->assertSame('FAILED', $order->status);
        $this->assertTrue((bool) data_get($order->meta, 'fulfillment.dead_lettered'));
        $this->assertSame('max retries reached', (string) data_get($order->meta, 'fulfillment.dead_letter_message'));
    }

    private function createPaidOrder(): Order
    {
        $category = Category::query()->create([
            'name' => 'Top Up Game',
            'slug' => 'top-up-job-retry',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'ML 86',
            'slug' => 'ml-86-job-retry',
            'sku' => 'ML-86-JOB-RETRY',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        $provider = Provider::query()->create([
            'code' => 'DIGIFLAZZ',
            'name' => 'Digiflazz',
            'is_active' => true,
        ]);

        return Order::query()->create([
            'order_code' => 'ORD-JOB-RETRY-001',
            'product_id' => $product->id,
            'product_type' => 'TOPUP',
            'customer_target' => '08123456789',
            'base_price' => 20000,
            'admin_fee' => 0,
            'margin' => 1500,
            'final_amount' => 21500,
            'status' => 'PAID',
            'meta' => [
                'candidates' => [
                    [
                        'provider_id' => $provider->id,
                        'provider_code' => 'DIGIFLAZZ',
                        'provider_product_code' => 'SKU-ML-86',
                    ],
                ],
            ],
        ]);
    }
}
