<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Margin;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ProviderPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_multifinance_quote_prioritizes_zero_admin_provider(): void
    {
        $setup = $this->seedMultifinanceProduct();

        $response = $this->postJson('/api/v1/orders/quote', [
            'product_id' => $setup['product']->id,
            'quantity' => 1,
            'customer_target' => '08123456789',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('code', 'QUOTE_CREATED')
            ->assertJsonPath('data.selected_provider.provider_code', 'DIGIFLAZZ')
            ->assertJsonPath('data.pricing.admin_fee', 0.0);
    }

    public function test_order_creation_is_idempotent_by_key(): void
    {
        $setup = $this->seedTopupProduct();

        $quoteResponse = $this->postJson('/api/v1/orders/quote', [
            'product_id' => $setup['product']->id,
            'quantity' => 1,
            'customer_target' => '1234567890',
        ]);

        $quoteResponse->assertOk()->assertJsonPath('code', 'QUOTE_CREATED');

        $quoteToken = (string) $quoteResponse->json('data.quote_token');

        $payload = [
            'quote_token' => $quoteToken,
            'idempotency_key' => 'idem-order-001',
        ];

        $first = $this->postJson('/api/v1/orders', $payload);
        $second = $this->postJson('/api/v1/orders', $payload);

        $first
            ->assertOk()
            ->assertJsonPath('code', 'ORDER_CREATED');

        $second
            ->assertOk()
            ->assertJsonPath('code', 'ORDER_ALREADY_EXISTS');

        $this->assertSame(
            (string) $first->json('data.order_code'),
            (string) $second->json('data.order_code')
        );
    }

    private function seedTopupProduct(): array
    {
        $category = Category::query()->create([
            'name' => 'Top Up Game',
            'slug' => 'topup-game',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'ML 86 Diamond',
            'slug' => 'ml-86-diamond',
            'sku' => 'ML-86-TEST',
            'type' => 'TOPUP',
            'is_active' => true,
        ]);

        Margin::query()->create([
            'category_id' => $category->id,
            'mode' => 'FLAT',
            'value' => 1000,
            'is_active' => true,
        ]);

        $digiflazz = Provider::query()->create([
            'code' => 'DIGIFLAZZ',
            'name' => 'Digiflazz',
            'is_active' => true,
        ]);

        $orderkuota = Provider::query()->create([
            'code' => 'ORDERKUOTA',
            'name' => 'Orderkuota',
            'is_active' => true,
        ]);

        ProviderPrice::query()->create([
            'provider_id' => $digiflazz->id,
            'product_id' => $product->id,
            'base_price' => 20000,
            'admin_fee' => 0,
            'commission' => 50,
            'is_active' => true,
        ]);

        ProviderPrice::query()->create([
            'provider_id' => $orderkuota->id,
            'product_id' => $product->id,
            'base_price' => 21000,
            'admin_fee' => 0,
            'commission' => 100,
            'is_active' => true,
        ]);

        return [
            'product' => $product,
        ];
    }

    private function seedMultifinanceProduct(): array
    {
        $category = Category::query()->create([
            'name' => 'Multifinance',
            'slug' => 'multifinance',
            'type' => 'MULTIFINANCE',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Tagihan Multifinance',
            'slug' => 'tagihan-multifinance',
            'sku' => 'MF-TEST-001',
            'type' => 'MULTIFINANCE',
            'is_active' => true,
        ]);

        Margin::query()->create([
            'category_id' => $category->id,
            'mode' => 'FLAT',
            'value' => 500,
            'is_active' => true,
        ]);

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

        ProviderPrice::query()->create([
            'provider_id' => $digiflazz->id,
            'product_id' => $product->id,
            'base_price' => 50000,
            'admin_fee' => 0,
            'commission' => 100,
            'is_active' => true,
        ]);

        ProviderPrice::query()->create([
            'provider_id' => $rajabiller->id,
            'product_id' => $product->id,
            'base_price' => 49500,
            'admin_fee' => 2000,
            'commission' => 800,
            'is_active' => true,
        ]);

        return [
            'product' => $product,
        ];
    }
}
