<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `transaction_status` ENUM('pending', 'processing', 'success', 'failed', 'expired') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `transaction_status` ENUM('pending', 'processing', 'success', 'failed') NOT NULL DEFAULT 'pending'");
    }
};
