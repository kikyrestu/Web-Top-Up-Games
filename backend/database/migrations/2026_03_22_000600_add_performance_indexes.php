<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS orders_order_code_idx ON orders (order_code)');
        DB::statement('CREATE INDEX IF NOT EXISTS payments_order_id_status_idx ON payments (order_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS payment_webhooks_gateway_event_key_idx ON payment_webhooks (gateway, event_key)');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX IF NOT EXISTS orders_meta_gin_idx ON orders USING GIN (meta)');
            DB::statement('CREATE INDEX IF NOT EXISTS audit_logs_payload_gin_idx ON audit_logs USING GIN (payload)');
            DB::statement("CREATE INDEX IF NOT EXISTS orders_meta_idempotency_idx ON orders ((meta->>'idempotency_key'))");
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS orders_order_code_idx');
        DB::statement('DROP INDEX IF EXISTS payments_order_id_status_idx');
        DB::statement('DROP INDEX IF EXISTS payment_webhooks_gateway_event_key_idx');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS orders_meta_gin_idx');
            DB::statement('DROP INDEX IF EXISTS audit_logs_payload_gin_idx');
            DB::statement('DROP INDEX IF EXISTS orders_meta_idempotency_idx');
        }
    }
};
