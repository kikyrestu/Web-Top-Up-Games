<?php

use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\AdminCmsController;
use App\Http\Controllers\Web\AdminAuditLogController;
use App\Http\Controllers\Web\AdminCatalogController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminNominalController;
use App\Http\Controllers\Web\AdminOrderController;
use App\Http\Controllers\Web\AdminPaymentManagementController;
use App\Http\Controllers\Web\AdminPricingController;
use App\Http\Controllers\Web\AdminReviewModerationController;
use App\Http\Controllers\Web\AdminSecurityEventController;
use App\Http\Controllers\Web\AdminSeoController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\OtpAuthController;
use App\Http\Controllers\Web\PublicPageController;
use App\Http\Controllers\Web\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
Route::get('/sitemap.xml', [PublicPageController::class, 'sitemap'])->name('public.sitemap');
Route::get('/robots.txt', [PublicPageController::class, 'robots'])->name('public.robots');
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
Route::post('/login-otp', [OtpAuthController::class, 'requestOtp'])
    ->middleware('global.rate:otp-login')
    ->name('account.login-otp.request');
Route::get('/verify-otp', [OtpAuthController::class, 'showVerify'])->name('account.verify-otp');
Route::post('/verify-otp', [OtpAuthController::class, 'verifyOtp'])
    ->middleware('global.rate:otp-verify')
    ->name('account.verify-otp.submit');
Route::post('/akun/logout', [OtpAuthController::class, 'logout'])->name('account.logout');

Route::prefix('akun')->name('account.')->middleware('web.auth')->group(function (): void {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/transaksi', [AccountController::class, 'transactions'])->name('transactions');
    Route::get('/transaksi/{orderCode}', [AccountController::class, 'transactionShow'])->name('transactions.show');
    Route::get('/profil', [AccountController::class, 'profile'])->name('profile');
    Route::post('/profil', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/ulasan', [AccountController::class, 'reviews'])->name('reviews');
    Route::post('/ulasan', [AccountController::class, 'storeReview'])
        ->middleware('global.rate:review-submit')
        ->name('reviews.store');
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/buildywebadmin/Login', [AdminAuthController::class, 'showLogin'])
        ->middleware('guest')
        ->name('login');
    Route::post('/buildywebadmin/Login', [AdminAuthController::class, 'login'])
        ->middleware('guest')
        ->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(['web.admin'])->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/alerts', [AdminDashboardController::class, 'alerts'])->name('dashboard.alerts');
        Route::get('/dashboard/metrics/excel', [AdminDashboardController::class, 'metricsExcel'])->name('dashboard.metrics.excel');
        Route::get('/dashboard/rate-limit/csv', [AdminDashboardController::class, 'rateLimitCsv'])->name('dashboard.rate-limit.csv');

        Route::get('/cms/pages', [AdminCmsController::class, 'pagesIndex'])->name('cms.pages.index');
        Route::get('/cms/pages/create', [AdminCmsController::class, 'pagesCreate'])->name('cms.pages.create');
        Route::post('/cms/pages', [AdminCmsController::class, 'pagesStore'])->name('cms.pages.store');
        Route::get('/cms/pages/{page}/edit', [AdminCmsController::class, 'pagesEdit'])->name('cms.pages.edit');
        Route::put('/cms/pages/{page}', [AdminCmsController::class, 'pagesUpdate'])->name('cms.pages.update');
        Route::delete('/cms/pages/{page}', [AdminCmsController::class, 'pagesDestroy'])->name('cms.pages.destroy');

        Route::get('/cms/banners', [AdminCmsController::class, 'bannersIndex'])->name('cms.banners.index');
        Route::get('/cms/banners/create', [AdminCmsController::class, 'bannersCreate'])->name('cms.banners.create');
        Route::post('/cms/banners', [AdminCmsController::class, 'bannersStore'])->name('cms.banners.store');
        Route::get('/cms/banners/{banner}/edit', [AdminCmsController::class, 'bannersEdit'])->name('cms.banners.edit');
        Route::put('/cms/banners/{banner}', [AdminCmsController::class, 'bannersUpdate'])->name('cms.banners.update');
        Route::delete('/cms/banners/{banner}', [AdminCmsController::class, 'bannersDestroy'])->name('cms.banners.destroy');

        Route::get('/catalog/categories', [AdminCatalogController::class, 'categoriesIndex'])->name('catalog.categories.index');
        Route::get('/catalog/categories/create', [AdminCatalogController::class, 'categoriesCreate'])->name('catalog.categories.create');
        Route::post('/catalog/categories', [AdminCatalogController::class, 'categoriesStore'])->name('catalog.categories.store');
        Route::get('/catalog/categories/{category}/edit', [AdminCatalogController::class, 'categoriesEdit'])->name('catalog.categories.edit');
        Route::put('/catalog/categories/{category}', [AdminCatalogController::class, 'categoriesUpdate'])->name('catalog.categories.update');
        Route::delete('/catalog/categories/{category}', [AdminCatalogController::class, 'categoriesDestroy'])->name('catalog.categories.destroy');

        Route::get('/catalog/products', [AdminCatalogController::class, 'productsIndex'])->name('catalog.products.index');
        Route::get('/catalog/products/create', [AdminCatalogController::class, 'productsCreate'])->name('catalog.products.create');
        Route::post('/catalog/products', [AdminCatalogController::class, 'productsStore'])->name('catalog.products.store');
        Route::get('/catalog/products/{product}/edit', [AdminCatalogController::class, 'productsEdit'])->name('catalog.products.edit');
        Route::put('/catalog/products/{product}', [AdminCatalogController::class, 'productsUpdate'])->name('catalog.products.update');
        Route::delete('/catalog/products/{product}', [AdminCatalogController::class, 'productsDestroy'])->name('catalog.products.destroy');

        Route::get('/catalog/providers', [AdminCatalogController::class, 'providersIndex'])->name('catalog.providers.index');
        Route::get('/catalog/providers/create', [AdminCatalogController::class, 'providersCreate'])->name('catalog.providers.create');
        Route::post('/catalog/providers', [AdminCatalogController::class, 'providersStore'])->name('catalog.providers.store');
        Route::get('/catalog/providers/{provider}/edit', [AdminCatalogController::class, 'providersEdit'])->name('catalog.providers.edit');
        Route::put('/catalog/providers/{provider}', [AdminCatalogController::class, 'providersUpdate'])->name('catalog.providers.update');
        Route::delete('/catalog/providers/{provider}', [AdminCatalogController::class, 'providersDestroy'])->name('catalog.providers.destroy');

        Route::get('/nominal/mappings', [AdminNominalController::class, 'mappingsIndex'])->name('nominal.mappings.index');
        Route::get('/nominal/mappings/create', [AdminNominalController::class, 'mappingsCreate'])->name('nominal.mappings.create');
        Route::post('/nominal/mappings', [AdminNominalController::class, 'mappingsStore'])->name('nominal.mappings.store');
        Route::get('/nominal/mappings/{mapping}/edit', [AdminNominalController::class, 'mappingsEdit'])->name('nominal.mappings.edit');
        Route::put('/nominal/mappings/{mapping}', [AdminNominalController::class, 'mappingsUpdate'])->name('nominal.mappings.update');
        Route::delete('/nominal/mappings/{mapping}', [AdminNominalController::class, 'mappingsDestroy'])->name('nominal.mappings.destroy');

        Route::get('/nominal/prices', [AdminNominalController::class, 'pricesIndex'])->name('nominal.prices.index');
        Route::get('/nominal/prices/create', [AdminNominalController::class, 'pricesCreate'])->name('nominal.prices.create');
        Route::post('/nominal/prices', [AdminNominalController::class, 'pricesStore'])->name('nominal.prices.store');
        Route::get('/nominal/prices/{price}/edit', [AdminNominalController::class, 'pricesEdit'])->name('nominal.prices.edit');
        Route::put('/nominal/prices/{price}', [AdminNominalController::class, 'pricesUpdate'])->name('nominal.prices.update');
        Route::delete('/nominal/prices/{price}', [AdminNominalController::class, 'pricesDestroy'])->name('nominal.prices.destroy');

        Route::get('/payment/gateways', [AdminPaymentManagementController::class, 'index'])->name('payment.gateways.index');
        Route::get('/payment/gateways/create', [AdminPaymentManagementController::class, 'create'])->name('payment.gateways.create');
        Route::post('/payment/gateways', [AdminPaymentManagementController::class, 'store'])->name('payment.gateways.store');
        Route::get('/payment/gateways/{gateway}/edit', [AdminPaymentManagementController::class, 'edit'])->name('payment.gateways.edit');
        Route::put('/payment/gateways/{gateway}', [AdminPaymentManagementController::class, 'update'])->name('payment.gateways.update');
        Route::delete('/payment/gateways/{gateway}', [AdminPaymentManagementController::class, 'destroy'])->name('payment.gateways.destroy');

        Route::get('/pricing/margins', [AdminPricingController::class, 'marginsIndex'])->name('pricing.margins.index');
        Route::get('/pricing/margins/create', [AdminPricingController::class, 'marginsCreate'])->name('pricing.margins.create');
        Route::post('/pricing/margins', [AdminPricingController::class, 'marginsStore'])->name('pricing.margins.store');
        Route::get('/pricing/margins/{margin}/edit', [AdminPricingController::class, 'marginsEdit'])->name('pricing.margins.edit');
        Route::put('/pricing/margins/{margin}', [AdminPricingController::class, 'marginsUpdate'])->name('pricing.margins.update');
        Route::delete('/pricing/margins/{margin}', [AdminPricingController::class, 'marginsDestroy'])->name('pricing.margins.destroy');

        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/export/csv', [AdminAuditLogController::class, 'exportCsv'])->name('audit-logs.export.csv');
        Route::get('/security-events', [AdminSecurityEventController::class, 'index'])->name('security-events.index');
        Route::get('/security-events/export/csv', [AdminSecurityEventController::class, 'exportCsv'])->name('security-events.export.csv');
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{orderCode}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{orderCode}/reprocess', [AdminOrderController::class, 'reprocess'])->name('orders.reprocess');

        Route::get('/reviews', [AdminReviewModerationController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/bulk-moderate', [AdminReviewModerationController::class, 'bulkModerate'])->name('reviews.bulk-moderate');
        Route::get('/reviews/{review}', [AdminReviewModerationController::class, 'show'])->name('reviews.show');
        Route::post('/reviews/{review}/approve', [AdminReviewModerationController::class, 'approve'])->name('reviews.approve');
        Route::post('/reviews/{review}/reject', [AdminReviewModerationController::class, 'reject'])->name('reviews.reject');

        Route::get('/seo', [AdminSeoController::class, 'index'])->name('seo.index');
        Route::get('/seo/create', [AdminSeoController::class, 'create'])->name('seo.create');
        Route::post('/seo', [AdminSeoController::class, 'store'])->name('seo.store');
        Route::get('/seo/{seo}/edit', [AdminSeoController::class, 'edit'])->name('seo.edit');
        Route::put('/seo/{seo}', [AdminSeoController::class, 'update'])->name('seo.update');
        Route::delete('/seo/{seo}', [AdminSeoController::class, 'destroy'])->name('seo.destroy');
    });
});
