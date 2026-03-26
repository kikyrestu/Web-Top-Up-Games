<?php
// fix bom safely
\ = ['.', 'app/', 'routes/', 'app/Http/Controllers/', 'app/Http/Controllers/Admin/'];
// Just clean category controller immediately
\ = 'app/Http/Controllers/Admin/CategoryController.php';
if (file_exists(\)) {
    \<?php

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

use App\Services\TripayService;
use Illuminate\Http\Request;

// --- FRONTEND ROUTES ---
Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/kategori/{id}', [FrontController::class, 'showCategory'])->name('front.category');
Route::get('/cek-pesanan', [FrontController::class, 'cekPesanan'])->name('front.cek-pesanan');
Route::post('/cek-pesanan', [FrontController::class, 'prosesCekPesanan'])->name('front.proses-cek-pesanan');

// Article routes
Route::get('/artikel', [ArticleController::class, 'index'])->name('front.article.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('front.article.show');

// Checkout routes
Route::get('/checkout', [FrontController::class, 'checkout'])->name('front.checkout');
Route::post('/checkout', [TransactionController::class, 'checkout'])->name('checkout');
Route::get('/transaction/{invoice}', [TransactionController::class, 'show'])->name('transaction.show');

// Webhook
Route::post('/callback/midtrans', function(\Illuminate\Http\Request $request) { \Log::info('Midtrans Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/doku', function(\Illuminate\Http\Request $request) { \Log::info('Doku Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/klikqris', function(\Illuminate\Http\Request $request) { \Log::info('KlikQRIS Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/tripay', function(Request $request, TripayService $tripayService) {
    return $tripayService->handleCallback($request);
});


// Secret Admin Login
Route::get('/admin/buildywebadmin/Login', function (Request $request) {
    if (!str_contains($_SERVER['REQUEST_URI'] ?? '', '?=AdminPanel')) {
        abort(404);
    }
    return view('admin.auth.login');
})->middleware('guest')->name('admin.secret.login');

// --- ADMIN / MEMBER DASHBOARD ---
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Admin CRUD routes
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);
    Route::resource('transactions', \App\Http\Controllers\Admin\TransactionController::class)->only(['index', 'show', 'update']);
    Route::resource('users', UserController::class);
    Route::resource('api-providers', ApiProviderController::class)->parameters(['api-providers' => 'apiProvider']);
    Route::resource('payment-gateways', PaymentGatewayController::class)->parameters(['payment-gateways' => 'paymentGateway']);
    
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



Route::get('/api/ppob/products', function(Request $request) {
    $categoryName = $request->query('category', 'pulsa');
    $providerName = $request->query('provider');

    $query = Product::where('is_active', true)
        ->whereHas('category', function($q) use ($categoryName) {
            $q->where('name', 'like', '%' . $categoryName . '%');
        });

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




 = file_get_contents(\);
    \<?php

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

use App\Services\TripayService;
use Illuminate\Http\Request;

// --- FRONTEND ROUTES ---
Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/kategori/{id}', [FrontController::class, 'showCategory'])->name('front.category');
Route::get('/cek-pesanan', [FrontController::class, 'cekPesanan'])->name('front.cek-pesanan');
Route::post('/cek-pesanan', [FrontController::class, 'prosesCekPesanan'])->name('front.proses-cek-pesanan');

// Article routes
Route::get('/artikel', [ArticleController::class, 'index'])->name('front.article.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('front.article.show');

// Checkout routes
Route::get('/checkout', [FrontController::class, 'checkout'])->name('front.checkout');
Route::post('/checkout', [TransactionController::class, 'checkout'])->name('checkout');
Route::get('/transaction/{invoice}', [TransactionController::class, 'show'])->name('transaction.show');

// Webhook
Route::post('/callback/midtrans', function(\Illuminate\Http\Request $request) { \Log::info('Midtrans Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/doku', function(\Illuminate\Http\Request $request) { \Log::info('Doku Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/klikqris', function(\Illuminate\Http\Request $request) { \Log::info('KlikQRIS Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/tripay', function(Request $request, TripayService $tripayService) {
    return $tripayService->handleCallback($request);
});


// Secret Admin Login
Route::get('/admin/buildywebadmin/Login', function (Request $request) {
    if (!str_contains($_SERVER['REQUEST_URI'] ?? '', '?=AdminPanel')) {
        abort(404);
    }
    return view('admin.auth.login');
})->middleware('guest')->name('admin.secret.login');

// --- ADMIN / MEMBER DASHBOARD ---
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Admin CRUD routes
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);
    Route::resource('transactions', \App\Http\Controllers\Admin\TransactionController::class)->only(['index', 'show', 'update']);
    Route::resource('users', UserController::class);
    Route::resource('api-providers', ApiProviderController::class)->parameters(['api-providers' => 'apiProvider']);
    Route::resource('payment-gateways', PaymentGatewayController::class)->parameters(['payment-gateways' => 'paymentGateway']);
    
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



Route::get('/api/ppob/products', function(Request $request) {
    $categoryName = $request->query('category', 'pulsa');
    $providerName = $request->query('provider');

    $query = Product::where('is_active', true)
        ->whereHas('category', function($q) use ($categoryName) {
            $q->where('name', 'like', '%' . $categoryName . '%');
        });

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




 = preg_replace('/^.*?<\?php/s', '<?php', \<?php

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

use App\Services\TripayService;
use Illuminate\Http\Request;

// --- FRONTEND ROUTES ---
Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/kategori/{id}', [FrontController::class, 'showCategory'])->name('front.category');
Route::get('/cek-pesanan', [FrontController::class, 'cekPesanan'])->name('front.cek-pesanan');
Route::post('/cek-pesanan', [FrontController::class, 'prosesCekPesanan'])->name('front.proses-cek-pesanan');

// Article routes
Route::get('/artikel', [ArticleController::class, 'index'])->name('front.article.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('front.article.show');

// Checkout routes
Route::get('/checkout', [FrontController::class, 'checkout'])->name('front.checkout');
Route::post('/checkout', [TransactionController::class, 'checkout'])->name('checkout');
Route::get('/transaction/{invoice}', [TransactionController::class, 'show'])->name('transaction.show');

// Webhook
Route::post('/callback/midtrans', function(\Illuminate\Http\Request $request) { \Log::info('Midtrans Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/doku', function(\Illuminate\Http\Request $request) { \Log::info('Doku Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/klikqris', function(\Illuminate\Http\Request $request) { \Log::info('KlikQRIS Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/tripay', function(Request $request, TripayService $tripayService) {
    return $tripayService->handleCallback($request);
});


// Secret Admin Login
Route::get('/admin/buildywebadmin/Login', function (Request $request) {
    if (!str_contains($_SERVER['REQUEST_URI'] ?? '', '?=AdminPanel')) {
        abort(404);
    }
    return view('admin.auth.login');
})->middleware('guest')->name('admin.secret.login');

// --- ADMIN / MEMBER DASHBOARD ---
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Admin CRUD routes
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);
    Route::resource('transactions', \App\Http\Controllers\Admin\TransactionController::class)->only(['index', 'show', 'update']);
    Route::resource('users', UserController::class);
    Route::resource('api-providers', ApiProviderController::class)->parameters(['api-providers' => 'apiProvider']);
    Route::resource('payment-gateways', PaymentGatewayController::class)->parameters(['payment-gateways' => 'paymentGateway']);
    
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



Route::get('/api/ppob/products', function(Request $request) {
    $categoryName = $request->query('category', 'pulsa');
    $providerName = $request->query('provider');

    $query = Product::where('is_active', true)
        ->whereHas('category', function($q) use ($categoryName) {
            $q->where('name', 'like', '%' . $categoryName . '%');
        });

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




);
    file_put_contents(\, \<?php

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

use App\Services\TripayService;
use Illuminate\Http\Request;

// --- FRONTEND ROUTES ---
Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/kategori/{id}', [FrontController::class, 'showCategory'])->name('front.category');
Route::get('/cek-pesanan', [FrontController::class, 'cekPesanan'])->name('front.cek-pesanan');
Route::post('/cek-pesanan', [FrontController::class, 'prosesCekPesanan'])->name('front.proses-cek-pesanan');

// Article routes
Route::get('/artikel', [ArticleController::class, 'index'])->name('front.article.index');
Route::get('/artikel/{slug}', [ArticleController::class, 'show'])->name('front.article.show');

// Checkout routes
Route::get('/checkout', [FrontController::class, 'checkout'])->name('front.checkout');
Route::post('/checkout', [TransactionController::class, 'checkout'])->name('checkout');
Route::get('/transaction/{invoice}', [TransactionController::class, 'show'])->name('transaction.show');

// Webhook
Route::post('/callback/midtrans', function(\Illuminate\Http\Request $request) { \Log::info('Midtrans Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/doku', function(\Illuminate\Http\Request $request) { \Log::info('Doku Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/klikqris', function(\Illuminate\Http\Request $request) { \Log::info('KlikQRIS Webhook:', $request->all()); return response()->json(['status' => 'ok']); });
Route::post('/callback/tripay', function(Request $request, TripayService $tripayService) {
    return $tripayService->handleCallback($request);
});


// Secret Admin Login
Route::get('/admin/buildywebadmin/Login', function (Request $request) {
    if (!str_contains($_SERVER['REQUEST_URI'] ?? '', '?=AdminPanel')) {
        abort(404);
    }
    return view('admin.auth.login');
})->middleware('guest')->name('admin.secret.login');

// --- ADMIN / MEMBER DASHBOARD ---
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Admin CRUD routes
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);
    Route::resource('transactions', \App\Http\Controllers\Admin\TransactionController::class)->only(['index', 'show', 'update']);
    Route::resource('users', UserController::class);
    Route::resource('api-providers', ApiProviderController::class)->parameters(['api-providers' => 'apiProvider']);
    Route::resource('payment-gateways', PaymentGatewayController::class)->parameters(['payment-gateways' => 'paymentGateway']);
    
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



Route::get('/api/ppob/products', function(Request $request) {
    $categoryName = $request->query('category', 'pulsa');
    $providerName = $request->query('provider');

    $query = Product::where('is_active', true)
        ->whereHas('category', function($q) use ($categoryName) {
            $q->where('name', 'like', '%' . $categoryName . '%');
        });

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




);
}