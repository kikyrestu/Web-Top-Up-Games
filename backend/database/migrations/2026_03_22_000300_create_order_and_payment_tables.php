<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_code', 50)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guest_session_id')->nullable()->constrained('guest_sessions')->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_type', 40);
            $table->string('customer_target', 120)->nullable();
            $table->decimal('base_price', 14, 2);
            $table->decimal('admin_fee', 14, 2)->default(0);
            $table->decimal('margin', 14, 2)->default(0);
            $table->decimal('final_amount', 14, 2);
            $table->string('status', 30)->default('PENDING');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });

        Schema::create('order_provider_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_no')->default(1);
            $table->string('status', 30)->default('PENDING');
            $table->string('provider_ref', 120)->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'attempt_no']);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 40);
            $table->string('gateway_reference', 120)->unique();
            $table->string('method', 50)->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('status', 30)->default('UNPAID');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('payment_webhooks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('gateway', 40);
            $table->string('event_key', 120)->nullable();
            $table->string('signature', 255)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->json('headers')->nullable();
            $table->json('payload');
            $table->timestamp('received_at');
            $table->timestamps();
            $table->index(['gateway', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_provider_attempts');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
