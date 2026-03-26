<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE api_providers MODIFY credentials LONGTEXT NULL');
            DB::statement('ALTER TABLE payment_gateways MODIFY credentials LONGTEXT NULL');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE api_providers ALTER COLUMN credentials TYPE TEXT');
            DB::statement('ALTER TABLE payment_gateways ALTER COLUMN credentials TYPE TEXT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left no-op. Reverting encrypted text back to JSON can fail
        // when existing rows contain non-JSON encrypted payloads.
    }
};
