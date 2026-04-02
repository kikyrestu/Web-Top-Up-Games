<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('description')->nullable();
            $table->string('type', 20)->default('percentage'); // percentage | flat
            $table->decimal('value', 15, 2)->default(0);       // % atau nominal
            $table->decimal('max_discount', 15, 2)->nullable(); // Max potongan (untuk %)
            $table->decimal('min_purchase', 15, 2)->default(0); // Minimal pembelian
            $table->integer('max_uses')->nullable();            // null = unlimited
            $table->integer('uses_count')->default(0);          // Sudah dipakai berapa kali
            $table->integer('max_uses_per_user')->default(1);   // Per user
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('vouchers'); }
};
