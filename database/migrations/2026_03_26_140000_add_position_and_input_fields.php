<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add position to banners for placement control (hero, ppob_promo, etc.)
        Schema::table('banners', function (Blueprint $table) {
            $table->string('position', 50)->default('hero')->after('is_active');
        });

        // Add input_fields JSON to categories for dynamic form config
        Schema::table('categories', function (Blueprint $table) {
            $table->json('input_fields')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('position');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('input_fields');
        });
    }
};
