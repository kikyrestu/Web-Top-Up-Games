<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('commission_type')->nullable()->after('sort_order');     // flat, percentage, auto
            $table->decimal('commission_value', 15, 2)->nullable()->after('commission_type');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('commission_type')->nullable()->after('is_active');      // flat, percentage, auto (override)
            $table->decimal('commission_value', 15, 2)->nullable()->after('commission_type');
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->decimal('commission_amount', 15, 2)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'commission_value']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'commission_value']);
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('commission_amount');
        });
    }
};
