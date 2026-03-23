<?php

use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\GlobalRateLimit;
use App\Http\Middleware\EnsureWebAuth;
use App\Http\Middleware\EnsureWebAdminRole;
use App\Http\Middleware\HandleIdempotency;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.role' => EnsureAdminRole::class,
            'global.rate' => GlobalRateLimit::class,
            'web.auth' => EnsureWebAuth::class,
            'web.admin' => EnsureWebAdminRole::class,
            'idempotency' => HandleIdempotency::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
