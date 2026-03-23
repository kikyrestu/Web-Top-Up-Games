<?php

use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\AdminAuditLogController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminOrderController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\OtpAuthController;
use App\Http\Controllers\Web\PublicPageController;
use App\Http\Controllers\Web\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/top-up', [PublicPageController::class, 'topUpIndex'])->name('public.topup.index');
Route::get('/top-up/{gameSlug}', [PublicPageController::class, 'topUpShow'])->name('public.topup.show');
Route::get('/ppob', [PublicPageController::class, 'ppobIndex'])->name('public.ppob.index');
Route::get('/ppob/{categorySlug}', [PublicPageController::class, 'ppobShow'])->name('public.ppob.show');
Route::get('/cek-transaksi', [PublicPageController::class, 'checkTransaction'])->name('public.check-transaction');
Route::post('/cek-transaksi', [PublicPageController::class, 'handleCheckTransaction'])->name('public.check-transaction.submit');
Route::get('/promo', [PublicPageController::class, 'promo'])->name('public.promo');
Route::get('/artikel', [PublicPageController::class, 'articleIndex'])->name('public.articles.index');
Route::get('/artikel/{slug}', [PublicPageController::class, 'articleShow'])->name('public.articles.show');
Route::get('/ulasan', [PublicPageController::class, 'reviewIndex'])->name('public.reviews.index');
Route::post('/checkout', [StorefrontController::class, 'checkout'])->name('storefront.checkout');
Route::get('/track/{orderCode}', [StorefrontController::class, 'track'])->name('storefront.track');
Route::get('/history', [StorefrontController::class, 'history'])->name('storefront.history');

Route::get('/login-otp', [OtpAuthController::class, 'showLogin'])->name('account.login-otp');
Route::post('/login-otp', [OtpAuthController::class, 'requestOtp'])->name('account.login-otp.request');
Route::get('/verify-otp', [OtpAuthController::class, 'showVerify'])->name('account.verify-otp');
Route::post('/verify-otp', [OtpAuthController::class, 'verifyOtp'])->name('account.verify-otp.submit');
Route::post('/akun/logout', [OtpAuthController::class, 'logout'])->name('account.logout');

Route::prefix('akun')->name('account.')->middleware('web.auth')->group(function (): void {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/transaksi', [AccountController::class, 'transactions'])->name('transactions');
    Route::get('/transaksi/{orderCode}', [AccountController::class, 'transactionShow'])->name('transactions.show');
    Route::get('/profil', [AccountController::class, 'profile'])->name('profile');
    Route::post('/profil', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/ulasan', [AccountController::class, 'reviews'])->name('reviews');
});

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
        Route::get('/dashboard/alerts', [AdminDashboardController::class, 'alerts'])->name('dashboard.alerts');
        Route::get('/dashboard/metrics/excel', [AdminDashboardController::class, 'metricsExcel'])->name('dashboard.metrics.excel');
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/export/csv', [AdminAuditLogController::class, 'exportCsv'])->name('audit-logs.export.csv');
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{orderCode}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{orderCode}/reprocess', [AdminOrderController::class, 'reprocess'])->name('orders.reprocess');
    });
});
