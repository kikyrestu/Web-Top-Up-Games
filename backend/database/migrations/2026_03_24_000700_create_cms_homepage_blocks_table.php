<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_homepage_blocks', function (Blueprint $table): void {
            $table->id();
            $table->string('section_key', 60)->default('MAIN');
            $table->string('block_type', 40);
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('target_url', 1024)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['block_type', 'is_active', 'sort_order'], 'cms_homepage_blocks_type_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_homepage_blocks');
    }
};
