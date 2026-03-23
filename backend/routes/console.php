<?php

use App\Domain\Catalog\Services\ProductSyncService;
use App\Domain\Audit\Services\AuditLogService;
use App\Models\IdempotencyRequest;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('providers:sync-products', function (ProductSyncService $syncService): void {
    $count = $syncService->syncAll();
    $this->info('Provider sync completed. Updated rows: '.$count);
})->purpose('Sync provider products and pricing into local catalog mapping');

Artisan::command('idempotency:purge-expired', function (AuditLogService $auditLogService): void {
    $deleted = 0;

    IdempotencyRequest::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->chunkById(500, function ($rows) use (&$deleted): void {
            $ids = $rows->pluck('id')->all();

            if ($ids === []) {
                return;
            }

            $deleted += IdempotencyRequest::query()->whereIn('id', $ids)->delete();
        });

    $auditLogService->write([
        'event_type' => 'IDEMPOTENCY_PURGE_COMPLETED',
        'actor_type' => 'SYSTEM',
        'actor_id' => null,
        'entity_type' => 'IDEMPOTENCY',
        'entity_id' => null,
        'payload' => [
            'deleted_records' => $deleted,
        ],
        'occurred_at' => now(),
    ]);

    $this->info('Expired idempotency records purged: '.$deleted);
})->purpose('Purge expired idempotency request records');

Schedule::command('providers:sync-products')
    ->everyFiveMinutes()
    ->withoutOverlapping();

Schedule::command('idempotency:purge-expired')
    ->hourly()
    ->withoutOverlapping();
