<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sync products from all active providers every hour
Schedule::command('providers:sync')->hourly()->withoutOverlapping();

// Prune expired OTP records daily
Schedule::command('otp:prune')->daily();
