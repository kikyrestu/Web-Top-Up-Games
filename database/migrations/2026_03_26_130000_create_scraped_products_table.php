<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraped_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_provider_id')->constrained('api_providers')->cascadeOnDelete();
            $table->string('provider_product_code');          // buyer_sku_code
            $table->string('product_name');
            $table->string('brand')->nullable();
            $table->string('category_name')->nullable();      // category from provider
            $table->string('type')->default('prepaid');        // prepaid / pasca
            $table->decimal('price', 15, 2)->default(0);      // harga modal dari provider
            $table->decimal('price_sell_suggestion', 15, 2)->default(0);
            $table->string('status_provider')->default('available'); // available, disturb, empty
            $table->boolean('is_imported')->default(false);
            $table->foreignId('imported_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['api_provider_id', 'provider_product_code'], 'scraped_provider_code_unique');
            $table->index(['brand']);
            $table->index(['type']);
            $table->index(['is_imported']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraped_products');
    }
};
