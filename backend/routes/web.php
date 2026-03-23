<?php

use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminOrderController;
use App\Http\Controllers\Web\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::post('/checkout', [StorefrontController::class, 'checkout'])->name('storefront.checkout');
Route::get('/track/{orderCode}', [StorefrontController::class, 'track'])->name('storefront.track');
Route::get('/history', [StorefrontController::class, 'history'])->name('storefront.history');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])
        ->middleware('guest')
        ->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('guest')
        ->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['web.admin'])->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{orderCode}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{orderCode}/reprocess', [AdminOrderController::class, 'reprocess'])->name('orders.reprocess');
    });
});
