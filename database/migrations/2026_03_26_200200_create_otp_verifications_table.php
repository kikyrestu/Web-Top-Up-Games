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
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('target'); // phone or email
            $table->string('code', 10);
            $table->string('type')->default('register'); // register, login, reset
            $table->foreignId('otp_provider_id')->nullable()->constrained('otp_providers')->nullOnDelete();
            $table->boolean('is_verified')->default(false);
            $table->integer('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index(['target', 'code', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
