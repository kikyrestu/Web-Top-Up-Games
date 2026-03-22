<?php

use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use App\Http\Controllers\Api\V1\QuoteController;
use App\Http\Controllers\Api\V1\ValidationController;
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

    Route::post('/validation/game-id', [ValidationController::class, 'gameId']);
    Route::post('/orders/quote', [QuoteController::class, 'store']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{orderCode}', [OrderController::class, 'show']);
    Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
    Route::post('/payments/webhook/{gateway}', [PaymentWebhookController::class, 'handle']);
});
