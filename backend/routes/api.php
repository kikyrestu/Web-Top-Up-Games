<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', static fn () => [
        'success' => true,
        'code' => 'API_HEALTH_OK',
        'message' => 'API is healthy',
        'data' => [
            'service' => 'buildyweb-backend',
        ],
    ]);
});
