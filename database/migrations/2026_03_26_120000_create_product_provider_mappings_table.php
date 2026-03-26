<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_provider_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_provider_id')->constrained('api_providers')->cascadeOnDelete();
            $table->string('provider_product_code');
            $table->decimal('price_capital', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'api_provider_id']);
            $table->index(['product_id', 'is_active']);
        });

        // Migrate existing single-provider product data into mapping rows.
        $legacyProducts = DB::table('products')
            ->whereNotNull('api_provider_id')
            ->whereNotNull('provider_product_code')
            ->select(['id', 'api_provider_id', 'provider_product_code', 'price_capital'])
            ->get();

        foreach ($legacyProducts as $product) {
            DB::table('product_provider_mappings')->updateOrInsert(
                [
                    'product_id' => $product->id,
                    'api_provider_id' => $product->api_provider_id,
                ],
                [
                    'provider_product_code' => $product->provider_product_code,
                    'price_capital' => $product->price_capital ?? 0,
                    'is_active' => true,
                    'priority' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreignId('api_provider_id')->nullable()->after('product_id')->constrained('api_providers')->nullOnDelete();
            $table->string('provider_product_code')->nullable()->after('api_provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('api_provider_id');
            $table->dropColumn('provider_product_code');
        });

        Schema::dropIfExists('product_provider_mappings');
    }
};
