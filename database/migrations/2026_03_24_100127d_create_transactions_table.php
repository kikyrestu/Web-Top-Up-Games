<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Guest checkout
            $table->string('customer_name')->nullable();
            $table->string('customer_contact')->nullable();
            $table->string('target_input'); // Phone, ID game, etc.
            $table->foreignId('payment_gateway_id')->nullable()->constrained('payment_gateways')->nullOnDelete();
            
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            
            // Statuses
            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'expired'])->default('unpaid');
            $table->enum('transaction_status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            
            $table->string('payment_reference')->nullable();
            $table->string('provider_reference')->nullable();
            $table->json('api_response')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
