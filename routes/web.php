<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\TransactionController;
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
Route::get('/halaman/{slug}', [FrontController::class, 'page'])->name('front.page');

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
Route::get('/transaction/{invoice}', [TransactionController::class, 'showInvoice'])->name('transaction.show');

// Generic Payment Gateway Webhook — handles ALL gateways dynamically
Route::post('/webhook/pg/{gatewayCode}', [TransactionController::class, 'handleWebhook'])->name('webhook.pg');


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
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Admin CRUD routes
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);
    Route::resource('transactions', \App\Http\Controllers\Admin\TransactionController::class)->only(['index', 'show', 'update']);
    Route::resource('users', UserController::class);
    Route::resource('api-providers', ApiProviderController::class)->parameters(['api-providers' => 'apiProvider']);
    Route::post('api-providers/{apiProvider}/test-connection', [ApiProviderController::class, 'testConnection'])->name('api-providers.test-connection');
    Route::resource('payment-gateways', PaymentGatewayController::class)->parameters(['payment-gateways' => 'paymentGateway']);
    Route::post('payment-gateways/{paymentGateway}/test-connection', [PaymentGatewayController::class, 'testConnection'])->name('payment-gateways.test-connection');
    
    // Product Sync
    Route::get('/product-sync', [\App\Http\Controllers\Admin\ProductSyncController::class, 'index'])->name('product-sync.index');
    Route::post('/product-sync/sync', [\App\Http\Controllers\Admin\ProductSyncController::class, 'sync'])->name('product-sync.sync');

    // Scraped Products
    Route::get('/scraped-products', [\App\Http\Controllers\Admin\ScrapedProductController::class, 'index'])->name('scraped-products.index');
    Route::post('/scraped-products/import', [\App\Http\Controllers\Admin\ScrapedProductController::class, 'import'])->name('scraped-products.import');

    // WhatsApp Bot
    Route::get('/whatsapp/qr', [\App\Http\Controllers\Admin\WhatsAppController::class, 'qrCode'])->name('whatsapp.qr');
    Route::get('/whatsapp/status', [\App\Http\Controllers\Admin\WhatsAppController::class, 'status'])->name('whatsapp.status');
    Route::post('/whatsapp/test', [\App\Http\Controllers\Admin\WhatsAppController::class, 'testSend'])->name('whatsapp.test');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
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

    $products = $query->orderBy('price_sell')->get(['id', 'name', 'price_sell']);
    
    return response()->json($products->map(function($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->price_sell
        ];
    }));
})->name('api.ppob.products');

// Guest Identity API
Route::prefix('api/v1')->group(function() {
    Route::post('/guest/session/init', [\App\Http\Controllers\Api\GuestSessionController::class, 'init'])->name('api.guest.init');
});
