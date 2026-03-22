<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('guest_session_id')->nullable()->constrained('guest_sessions')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('content');
            $table->string('status', 30)->default('PENDING_APPROVAL');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'status', 'created_at']);
        });

        Schema::create('review_moderations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30);
            $table->text('reason')->nullable();
            $table->timestamp('moderated_at');
            $table->timestamps();
        });

        Schema::create('cms_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type', 40)->default('PAGE');
            $table->longText('content')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cms_banners', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('position', 30)->default('HERO');
            $table->string('image_path');
            $table->string('target_url', 1024)->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('seo_meta', function (Blueprint $table): void {
            $table->id();
            $table->string('entity_type', 40);
            $table->unsignedBigInteger('entity_id');
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_title', 255)->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image_path')->nullable();
            $table->timestamps();
            $table->unique(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
        Schema::dropIfExists('cms_banners');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('review_moderations');
        Schema::dropIfExists('reviews');
    }
};
