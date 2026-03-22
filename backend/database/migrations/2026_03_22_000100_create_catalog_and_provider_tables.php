<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 40)->default('TOPUP');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku', 100)->unique();
            $table->string('type', 40)->default('TOPUP');
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['category_id', 'type']);
        });

        Schema::create('provider_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('provider_product_code', 120);
            $table->string('provider_product_name');
            $table->boolean('is_available')->default(true);
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->unique(['provider_id', 'provider_product_code']);
            $table->unique(['provider_id', 'product_id']);
        });

        Schema::create('provider_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_price', 14, 2);
            $table->decimal('admin_fee', 14, 2)->default(0);
            $table->decimal('commission', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('provider_updated_at')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'provider_id', 'updated_at']);
        });

        Schema::create('provider_health_checks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('status', 30)->default('UNKNOWN');
            $table->unsignedSmallInteger('response_time_ms')->nullable();
            $table->unsignedSmallInteger('error_rate')->nullable();
            $table->timestamp('checked_at');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['provider_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_health_checks');
        Schema::dropIfExists('provider_prices');
        Schema::dropIfExists('provider_products');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('providers');
    }
};
