<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ApiProviderController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;

use App\Models\Category;
use App\Models\Article as ArticleModel;
use Illuminate\Http\Request;

// --- FRONTEND ROUTES ---
Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/top-up-game', [FrontController::class, 'topUpGame'])->name('front.top-up-game');
Route::get('/ppob', [FrontController::class, 'ppob'])->name('front.ppob');
Route::get('/kategori/{id}', [FrontController::class, 'showCategory'])->name('front.category');
Route::get('/cek-pesanan', [FrontController::class, 'cekPesanan'])->name('front.cek-pesanan');
Route::post('/cek-pesanan', [FrontController::class, 'prosesCekPesanan'])->name('front.proses-cek-pesanan');

// Article routes
Route::get('/artikel', [ArticleController::class, 'index'])->name('front.article.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('front.article.show');

Route::get('/feed.xml', function () {
    $articles = ArticleModel::query()
        ->where('is_published', true)
        ->select(['title', 'slug', 'content', 'created_at', 'updated_at'])
        ->orderByDesc('created_at')
        ->limit(30)
        ->get();

    return response()
        ->view('rss.index', ['articles' => $articles])
        ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
})->name('front.feed');

Route::get('/sitemap.xml', function () {
    $staticPageSlugs = [
        'daftar-harga',
        'faq',
        'kontak',
        'syarat-ketentuan',
        'kebijakan-privasi',
    ];

    $staticUrls = [
        [
            'loc' => route('front.index'),
            'lastmod' => now()->toDateString(),
            'priority' => '1.0',
            'changefreq' => 'daily',
        ],
        [
            'loc' => route('front.article.index'),
            'lastmod' => now()->toDateString(),
            'priority' => '0.7',
            'changefreq' => 'daily',
        ],
    ];

    foreach ($staticPageSlugs as $slug) {
        $staticUrls[] = [
            'loc' => route('front.page', $slug),
            'lastmod' => now()->toDateString(),
            'priority' => '0.4',
            'changefreq' => 'monthly',
        ];
    }

    $categoryUrls = Category::query()
        ->where('is_active', true)
        ->select(['slug', 'id', 'updated_at'])
        ->get()
        ->map(function ($category) {
            return [
                'loc' => route('front.category', $category->slug ?: $category->id),
                'lastmod' => optional($category->updated_at)->toDateString() ?? now()->toDateString(),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ];
        })
        ->values();

    $articleUrls = ArticleModel::query()
        ->where('is_published', true)
        ->select(['slug', 'updated_at'])
        ->orderByDesc('updated_at')
        ->get()
        ->map(function ($article) {
            return [
                'loc' => route('front.article.show', $article->slug),
                'lastmod' => optional($article->updated_at)->toDateString() ?? now()->toDateString(),
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        })
        ->values();

    $noindexUrls = collect([
        route('front.cek-pesanan'),
        route('front.checkout'),
    ]);

    $sitemapUrls = collect($staticUrls)
        ->concat($categoryUrls)
        ->concat($articleUrls)
        ->unique('loc')
        ->reject(function ($url) use ($noindexUrls) {
            return $noindexUrls->contains($url['loc']);
        })
        ->values();

    return response()
        ->view('sitemap.index', [
            'urls' => $sitemapUrls,
        ])
        ->header('Content-Type', 'application/xml');
})->name('front.sitemap');

// Checkout routes
Route::get('/checkout', [FrontController::class, 'checkout'])->name('front.checkout');
Route::post('/checkout', [TransactionController::class, 'checkout'])->name('checkout');
Route::post('/api/inquiry', [InquiryController::class, 'inquiry'])->name('api.inquiry');
Route::post('/api/validate-game-id', [\App\Http\Controllers\GameIdValidationController::class, 'validate'])->name('api.validate-game-id');
Route::get('/transaction/{invoice}', [TransactionController::class, 'showInvoice'])->name('transaction.show');
Route::get('/transaction/{invoice}/receipt', [TransactionController::class, 'showReceipt'])->name('transaction.receipt');
Route::get('/promo', [\App\Http\Controllers\Front\PromoController::class, 'index'])->name('front.promo');
Route::get('/kalkulator', [\App\Http\Controllers\FrontController::class, 'calculator'])->name('front.calculator');

// Generic Payment Gateway Webhook — handles ALL gateways dynamically
Route::post('/webhook/pg/{gatewayCode}', [TransactionController::class, 'handleWebhook'])->name('webhook.pg');
Route::post('/webhook/provider/{providerCode}', [\App\Http\Controllers\ProviderWebhookController::class, 'handle'])->name('webhook.provider');


// Secret Admin Login
Route::get('/admin/buildywebadmin/Login', function (Request $request) {
    if (!str_contains($_SERVER['REQUEST_URI'] ?? '', '?=AdminPanel')) {
        return "URL is: " . $request->fullUrl();
    }
    return view('admin.auth.login');
})->middleware('guest')->name('admin.secret.login');

// --- ADMIN / CUSTOMER DASHBOARD REDIRECT ---
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('member.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// --- MEMBER DASHBOARD ROUTES ---
Route::middleware(['auth', 'verified'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/transactions', [\App\Http\Controllers\CustomerController::class, 'transactions'])->name('transactions');
    Route::get('/transactions/{invoice}', [\App\Http\Controllers\CustomerController::class, 'transactionDetail'])->name('transactions.show');
    Route::get('/profile', [\App\Http\Controllers\CustomerController::class, 'profile'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\CustomerController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\CustomerController::class, 'updatePassword'])->name('profile.password');
    Route::get('/favorites', [\App\Http\Controllers\CustomerController::class, 'favorites'])->name('favorites');
    Route::post('/favorites/{category}', [\App\Http\Controllers\CustomerController::class, 'toggleFavorite'])->name('favorites.toggle');
    Route::get('/wallet', [\App\Http\Controllers\WalletController::class, 'index'])->name('wallet');
    Route::post('/wallet/topup', [\App\Http\Controllers\WalletController::class, 'topUp'])->name('wallet.topup');
    // Commission & Referral
    Route::get('/commission', [\App\Http\Controllers\Member\CommissionController::class, 'index'])->name('commission');
    Route::post('/commission/withdraw', [\App\Http\Controllers\Member\CommissionController::class, 'withdraw'])->name('commission.withdraw');
    // Reviews (AJAX from invoice popup)
    Route::post('/review', [\App\Http\Controllers\Member\ReviewController::class, 'store'])->name('review.store');
});



Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // TEMPORARY MIGRATE TRIGGER
    Route::get('/artisan-trigger', function() {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'PageSeeder', '--force' => true]);
        return 'Migration and Seed Success: ' . \Illuminate\Support\Facades\Artisan::output();
    });
    // Admin CRUD routes
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::delete('categories/bulk-destroy', [CategoryController::class, 'destroyBulk'])->name('categories.destroy-bulk');
    Route::resource('categories', CategoryController::class);
    
    Route::delete('products/bulk-destroy', [ProductController::class, 'destroyBulk'])->name('products.destroy-bulk');
    Route::resource('products', ProductController::class);
    Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);
    Route::resource('pages', \App\Http\Controllers\Admin\PageController::class)->except(['show']);
    Route::resource('transactions', \App\Http\Controllers\Admin\TransactionController::class)->only(['index', 'show', 'update']);
    Route::resource('users', UserController::class);
    Route::resource('api-providers', ApiProviderController::class)->parameters(['api-providers' => 'apiProvider']);
    Route::post('api-providers/{apiProvider}/test-connection', [ApiProviderController::class, 'testConnection'])->name('api-providers.test-connection');
    Route::resource('payment-gateways', PaymentGatewayController::class)->parameters(['payment-gateways' => 'paymentGateway']);
    Route::post('payment-gateways/{paymentGateway}/test-connection', [PaymentGatewayController::class, 'testConnection'])->name('payment-gateways.test-connection');
    
    Route::resource('otp-providers', \App\Http\Controllers\Admin\OtpProviderController::class)->parameters(['otp-providers' => 'otpProvider']);
    Route::post('otp-providers/{otpProvider}/test', [\App\Http\Controllers\Admin\OtpProviderController::class, 'testConnection'])->name('otp-providers.test');
    
    // Product Sync
    Route::get('/product-sync', [\App\Http\Controllers\Admin\ProductSyncController::class, 'index'])->name('product-sync.index');
    Route::post('/product-sync/sync', [\App\Http\Controllers\Admin\ProductSyncController::class, 'sync'])->name('product-sync.sync');
    Route::delete('/product-sync/empty', [\App\Http\Controllers\Admin\ProductSyncController::class, 'emptySync'])->name('product-sync.empty');

    // Scraped Products
    Route::get('/scraped-products', [\App\Http\Controllers\Admin\ScrapedProductController::class, 'index'])->name('scraped-products.index');
    Route::post('/scraped-products/import', [\App\Http\Controllers\Admin\ScrapedProductController::class, 'import'])->name('scraped-products.import');

    // WhatsApp Bot
    Route::get('/whatsapp/qr', [\App\Http\Controllers\Admin\WhatsAppController::class, 'qrCode'])->name('whatsapp.qr');
    Route::get('/whatsapp/status', [\App\Http\Controllers\Admin\WhatsAppController::class, 'status'])->name('whatsapp.status');
    Route::post('/whatsapp/test', [\App\Http\Controllers\Admin\WhatsAppController::class, 'testSend'])->name('whatsapp.test');
    Route::post('/whatsapp/start', [\App\Http\Controllers\Admin\WhatsAppController::class, 'startBot'])->name('whatsapp.start');
    Route::post('/whatsapp/stop', [\App\Http\Controllers\Admin\WhatsAppController::class, 'stopBot'])->name('whatsapp.stop');
    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Monitoring
    Route::get('/monitoring', [\App\Http\Controllers\Admin\MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/bots', [\App\Http\Controllers\Admin\MonitoringController::class, 'checkBot'])->name('monitoring.bots');
    Route::get('/monitoring/provider/{id}', [\App\Http\Controllers\Admin\MonitoringController::class, 'checkProvider'])->name('monitoring.provider');
    Route::get('/monitoring/gateway/{id}', [\App\Http\Controllers\Admin\MonitoringController::class, 'checkGateway'])->name('monitoring.gateway');
    Route::post('/monitoring/wa-bot/control', [\App\Http\Controllers\Admin\MonitoringController::class, 'waBotControl'])->name('monitoring.wa-bot.control');
    Route::get('/monitoring/wa-bot/status', [\App\Http\Controllers\Admin\MonitoringController::class, 'waBotPm2Status'])->name('monitoring.wa-bot.pm2-status');
    Route::get('/monitoring/wa-bot/qr', [\App\Http\Controllers\Admin\MonitoringController::class, 'waBotQr'])->name('monitoring.wa-bot.qr');
    // Vouchers
    Route::resource('vouchers', \App\Http\Controllers\Admin\VoucherController::class);
    Route::post('vouchers/validate', [\App\Http\Controllers\Admin\VoucherController::class, 'validate'])->name('vouchers.validate');
    // Reviews moderation
    Route::get('reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}/approve', [\App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
    Route::patch('reviews/{review}/reject', [\App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Logs (Spatie Activitylog etc can be added later)
});

// --- BREEZE PROFILE ---
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';




use App\Models\Product;



Route::middleware('throttle:60,1')->get('/api/ppob/products', function(Request $request) {
    $categoryId = $request->query('category_id');
    $categoryName = $request->query('category', 'pulsa');
    $providerName = $request->query('provider');

    $query = Product::where('is_active', true);

    if ($categoryId) {
        $query->where('category_id', $categoryId);
    } else {
        $query->whereHas('category', function($q) use ($categoryName) {
            $q->where('name', 'like', '%' . $categoryName . '%');
        });
    }

    if ($providerName) {
        $query->where('name', 'like', '%' . $providerName . '%');
    }

    $products = $query->orderBy('price_sell')->get();
    
    return response()->json($products->map(function($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->price_sell,
            'product_type' => $p->product_type ?? null,
            'product_group' => $p->product_group ?? null,
        ];
    }));
})->name('api.ppob.products');

// Catch-all route for static pages
Route::get('/{slug}', [\App\Http\Controllers\FrontController::class, 'page'])->name('front.page');

// Guest Identity API
Route::prefix('api/v1')->group(function() {
    Route::post('/guest/session/init', [\App\Http\Controllers\Api\GuestSessionController::class, 'init'])->name('api.guest.init');
    // Transaction status polling (for invoice page real-time refresh)
    Route::get('/transaction/{invoice}/status', function($invoice) {
        $tx = \App\Models\Transaction::where('invoice_number', $invoice)->firstOrFail();
        return response()->json([
            'transaction_status' => $tx->transaction_status,
            'payment_status'     => $tx->payment_status,
        ]);
    })->middleware('throttle:30,1')->name('api.transaction.status');
});
