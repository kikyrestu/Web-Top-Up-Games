<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mode', 20)->default('FLAT');
            $table->decimal('value', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['product_id', 'category_id', 'is_active']);
        });

        Schema::create('margins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mode', 20)->default('FLAT');
            $table->decimal('value', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['product_id', 'category_id', 'is_active']);
        });

        Schema::create('pricing_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_type', 40);
            $table->foreignId('selected_provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->json('candidates_snapshot');
            $table->string('decision_reason', 255)->nullable();
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
        });

        Schema::create('devices', function (Blueprint $table): void {
            $table->id();
            $table->string('fingerprint_hash', 128)->unique();
            $table->string('last_known_ip', 64)->nullable();
            $table->string('last_user_agent', 1024)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('guest_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->string('identity_key', 120);
            $table->string('channel', 20)->default('WA');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['identity_key', 'expires_at']);
        });

        Schema::create('otp_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('channel', 20);
            $table->string('destination', 120);
            $table->string('code_hash', 255);
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->string('request_ip', 64)->nullable();
            $table->string('user_agent', 1024)->nullable();
            $table->timestamps();
            $table->index(['destination', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_requests');
        Schema::dropIfExists('guest_sessions');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('pricing_logs');
        Schema::dropIfExists('margins');
        Schema::dropIfExists('commissions');
    }
};
