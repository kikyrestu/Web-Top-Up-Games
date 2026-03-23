<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_limit_metrics', function (Blueprint $table): void {
            $table->id();
            $table->string('profile', 60);
            $table->timestamp('hour_bucket');
            $table->unsignedInteger('hits')->default(0);
            $table->unsignedInteger('blocked')->default(0);
            $table->timestamps();

            $table->unique(['profile', 'hour_bucket']);
            $table->index(['hour_bucket']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_limit_metrics');
    }
};
