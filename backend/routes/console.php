<?php

use App\Domain\Catalog\Services\ProductSyncService;
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

Schedule::command('providers:sync-products')
    ->everyFiveMinutes()
    ->withoutOverlapping();
