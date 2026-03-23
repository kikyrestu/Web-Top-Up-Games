<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permission_matrix', function (Blueprint $table): void {
            $table->id();
            $table->string('role', 30);
            $table->string('permission_key', 120);
            $table->boolean('is_allowed')->default(true);
            $table->timestamps();

            $table->unique(['role', 'permission_key']);
            $table->index(['role', 'is_allowed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission_matrix');
    }
};
