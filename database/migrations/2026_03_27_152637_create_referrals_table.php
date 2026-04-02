<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id'); // Yang ngajak (punya kode referral)
            $table->unsignedBigInteger('referee_id');  // Yang diajak (pakai kode referral)
            $table->string('status', 20)->default('pending'); // pending | active | rewarded
            $table->decimal('bonus_amount', 15, 2)->default(0); // komisi yang didapat dari referee
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamps();

            $table->foreign('referrer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('referee_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique('referee_id'); // 1 user hanya bisa direferral 1 kali
            $table->index('referrer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
