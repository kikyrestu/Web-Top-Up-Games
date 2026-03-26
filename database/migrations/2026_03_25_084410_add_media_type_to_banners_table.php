<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('media_type')->default('image')->after('title'); // image, video, embed, html
            $table->text('media_content')->nullable()->after('media_type'); // to store html, embed links, or local video path
            $table->string('image')->nullable()->change(); // Make existing image pillar nullable since we might use html or video
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'media_content']);
            $table->string('image')->nullable(false)->change();
        });
    }
};