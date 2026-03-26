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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_provider_id')->nullable()->constrained('api_providers')->nullOnDelete();
            $table->string('name');
            $table->string('provider_product_code')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price_capital', 15, 2)->default(0);
            $table->decimal('price_sell', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
