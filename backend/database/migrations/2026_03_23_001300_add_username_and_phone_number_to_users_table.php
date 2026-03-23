<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 40)->nullable()->after('email')->unique();
            }

            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number', 25)->nullable()->after('username')->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'phone_number')) {
                $table->dropUnique('users_phone_number_unique');
                $table->dropColumn('phone_number');
            }

            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique('users_username_unique');
                $table->dropColumn('username');
            }
        });
    }
};
