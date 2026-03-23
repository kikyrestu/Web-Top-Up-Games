<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_operation_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_type', 40);
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30)->nullable();
            $table->decimal('refund_amount', 14, 2)->nullable();
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();

            $table->index(['order_id', 'acted_at']);
            $table->index(['action_type', 'acted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_operation_logs');
    }
};
