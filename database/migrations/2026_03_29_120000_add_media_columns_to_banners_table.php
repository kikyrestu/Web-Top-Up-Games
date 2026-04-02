<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'media_type')) {
                $table->string('media_type')->default('image')->after('link');
            }
            if (!Schema::hasColumn('banners', 'media_content')) {
                $table->text('media_content')->nullable()->after('media_type');
            }
            if (!Schema::hasColumn('banners', 'position')) {
                $table->string('position')->default('hero')->after('media_content');
            }
        });

        // Make image nullable manually to avoid doctrine/dbal requirement if not installed
        Illuminate\Support\Facades\DB::statement('ALTER TABLE banners MODIFY image VARCHAR(255) NULL;');
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'media_content', 'position']);
        });
    }
};
