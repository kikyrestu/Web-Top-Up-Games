<?php

use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\AdminBootstrapController;
use App\Http\Controllers\Api\V1\AuthTokenController;
use App\Http\Controllers\Api\V1\Admin\OrderProviderAttemptController;
use App\Http\Controllers\Api\V1\Admin\OrderReprocessController;
use App\Http\Controllers\Api\V1\Admin\SystemOpsController;
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
    Route::post('/admin/bootstrap', [AdminBootstrapController::class, 'store'])
        ->middleware('throttle:admin-bootstrap');
    Route::post('/auth/token/login', [AuthTokenController::class, 'login'])
        ->middleware('throttle:auth-token-login');
    Route::post('/orders/quote', [QuoteController::class, 'store']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{orderCode}', [OrderController::class, 'show']);
    Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
    Route::get('/payments/{gatewayReference}/status', [PaymentController::class, 'status']);
    Route::post('/payments/webhook/{gateway}', [PaymentWebhookController::class, 'handle']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/auth/me', [AuthTokenController::class, 'me']);
        Route::post('/auth/token/revoke-all', [AuthTokenController::class, 'revokeAll']);
        Route::post('/auth/token/logout', [AuthTokenController::class, 'logout']);
    });

    Route::prefix('admin')->middleware(['auth:sanctum', 'admin.role'])->group(function (): void {
        Route::post('/providers/sync-products', [SystemOpsController::class, 'syncProviders']);
        Route::get('/dashboard/overview', [SystemOpsController::class, 'dashboardOverview']);
        Route::get('/dashboard/metrics', [SystemOpsController::class, 'dashboardMetrics']);
        Route::get('/dashboard/alerts', [SystemOpsController::class, 'dashboardAlerts']);
        Route::get('/dashboard/metrics/excel', [SystemOpsController::class, 'dashboardMetricsExcel']);
        Route::get('/orders/{orderCode}/provider-attempts', [OrderProviderAttemptController::class, 'index']);
        Route::post('/orders/{orderCode}/reprocess', [OrderReprocessController::class, 'store']);
    });
});
