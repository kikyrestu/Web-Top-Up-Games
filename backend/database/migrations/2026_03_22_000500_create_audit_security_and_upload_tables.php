<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('event_type', 80);
            $table->string('actor_type', 20)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('entity_type', 80)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('request_id', 80)->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 1024)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['event_type', 'occurred_at']);
        });

        Schema::create('file_upload_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('actor_type', 20);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('file_size');
            $table->string('sha256_checksum', 128)->nullable();
            $table->string('upload_ip', 64)->nullable();
            $table->string('user_agent', 1024)->nullable();
            $table->string('verdict', 30)->default('ACCEPTED');
            $table->string('reason', 255)->nullable();
            $table->timestamps();
            $table->index(['verdict', 'created_at']);
        });

        Schema::create('security_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_code', 80);
            $table->string('severity', 20)->default('LOW');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 1024)->nullable();
            $table->unsignedSmallInteger('risk_score')->default(0);
            $table->json('context')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['severity', 'occurred_at']);
            $table->index(['event_code', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');
        Schema::dropIfExists('file_upload_logs');
        Schema::dropIfExists('audit_logs');
    }
};
