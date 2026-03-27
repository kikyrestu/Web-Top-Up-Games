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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('whatsapp')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('whatsapp');
            $table->decimal('wallet_balance', 15, 2)->default(0)->after('is_admin');
            $table->boolean('is_verified')->default(false)->after('wallet_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'whatsapp', 'avatar', 'wallet_balance', 'is_verified']);
        });
    }
};
