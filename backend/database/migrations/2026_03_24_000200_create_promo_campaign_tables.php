<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('code', 60)->unique();
            $table->string('campaign_type', 20)->default('VOUCHER');
            $table->string('discount_mode', 20)->default('FLAT');
            $table->decimal('discount_value', 14, 2)->default(0);
            $table->decimal('min_order_amount', 14, 2)->default(0);
            $table->decimal('max_discount_amount', 14, 2)->nullable();
            $table->unsignedInteger('quota_total')->nullable();
            $table->unsignedInteger('quota_per_user')->nullable();
            $table->string('scope', 20)->default('GLOBAL');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'start_at', 'end_at']);
            $table->index(['scope', 'product_id', 'category_id']);
        });

        Schema::create('promo_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('promo_campaign_id')->constrained('promo_campaigns')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('campaign_code', 60);
            $table->string('campaign_type', 20);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('cashback_amount', 14, 2)->default(0);
            $table->timestamp('redeemed_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['promo_campaign_id', 'redeemed_at']);
            $table->index(['campaign_code', 'redeemed_at']);
            $table->index(['user_id', 'promo_campaign_id']);
            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_redemptions');
        Schema::dropIfExists('promo_campaigns');
    }
};
