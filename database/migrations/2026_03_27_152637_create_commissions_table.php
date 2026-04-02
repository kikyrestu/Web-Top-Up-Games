<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');           // Pemilik komisi
            $table->unsignedBigInteger('transaction_id')->nullable(); // Dari transaksi mana
            $table->unsignedBigInteger('referral_id')->nullable();    // Dari referral mana
            $table->string('type', 30)->default('transaction'); // transaction | referral_bonus | withdrawal
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 20)->default('pending'); // pending | approved | paid | cancelled
            $table->text('note')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
