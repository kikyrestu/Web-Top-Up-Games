<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\Article;
use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index(Request $request)
    {
        $searchQuery = trim((string) $request->query('q', ''));

        // Setup Banners (hero position only for main slider)
        $banners = Banner::where('is_active', true)->where('position', 'hero')->orderBy('order')->get();

        // PPOB Promo Banner (left card in section)
        $ppobPromoBanner = Banner::where('is_active', true)->where('position', 'ppob_promo')->first();

        // Game Popular
        $popularGames = Category::where('is_active', true)
                                ->gameTypes()
                                ->where('is_popular', true)
                                ->orderBy('name')
                                ->get();

        // PPOB categories for tabs — normalize type variants so 'emoney'/'e-money'
        // and 'ppob'/seterusnya semuanya masuk ke widget
        $ppobTypeNormalize = [
            'emoney'   => 'e-money',
            'ewallet'  => 'e-money',
            'e-wallet' => 'e-money',
            'ppob'     => 'tagihan',   // Merge PPOB (internet pascabayar dll) into Tagihan tab
        ];

        $ppobCategories = Category::where('is_active', true)
            ->nonGameTypes()
            ->orderBy('name')
            ->get()
            ->map(function ($cat) use ($ppobTypeNormalize) {
                // Normalize type to a canonical form
                $normalized = strtolower(trim($cat->type ?? ''));
                $cat->type  = $ppobTypeNormalize[$normalized] ?? $normalized;
                return $cat;
            });

        $ppobGrouped = $ppobCategories->groupBy('type');

        // Urutkan agar: pulsa, e-money, tagihan, tv (max 4 tab)
        $preferredOrder = ['pulsa', 'e-money', 'tagihan', 'tv'];
        $remaining = $ppobGrouped->reject(fn($v, $k) => in_array($k, $preferredOrder));
        // Merge any extra types into 'tagihan' to enforce max 4 tabs
        if ($remaining->isNotEmpty()) {
            $tagihanItems = $ppobGrouped->get('tagihan', collect());
            foreach ($remaining as $items) {
                $tagihanItems = $tagihanItems->merge($items);
            }
            $ppobGrouped->put('tagihan', $tagihanItems);
        }
        $ppobGrouped = collect($preferredOrder)
            ->filter(fn($k) => $ppobGrouped->has($k))
            ->mapWithKeys(fn($k) => [$k => $ppobGrouped->get($k)]);


        // Semua Game
        $allGamesQuery = Category::where('is_active', true)
                            ->gameTypes()
                            ->orderBy('name');
                            
        if ($searchQuery !== '') {
            $allGamesQuery->where('name', 'like', '%' . $searchQuery . '%');
        }
        $allGames = $allGamesQuery->get();

        $searchCategories = collect();
        $searchProducts = collect();
        if ($searchQuery !== '') {
            $searchCategories = Category::where('is_active', true)
                ->where('name', 'like', '%' . $searchQuery . '%')
                ->orderBy('name')
                ->limit(8)
                ->get();

            $searchProducts = Product::with(['category', 'providerMappings'])
                ->where('is_active', true)
                ->where(function ($q) use ($searchQuery) {
                    $q->where('name', 'like', '%' . $searchQuery . '%')
                        ->orWhereHas('providerMappings', function ($mappingQuery) use ($searchQuery) {
                            $mappingQuery->where('provider_product_code', 'like', '%' . $searchQuery . '%');
                        });
                })
                ->whereHas('category', function ($q) {
                    $q->where('is_active', true);
                })
                ->orderBy('price_sell')
                ->limit(12)
                ->get();
        }

        // Kategori By Type
        $selulerGames = Category::where('is_active', true)->where('type', 'seluler')->orderBy('name')->get();
        $pcGames = Category::where('is_active', true)->where('type', 'pc')->orderBy('name')->get();
        $voucherGames = Category::where('is_active', true)->where('type', 'voucher')->orderBy('name')->get();

        // All Categories (games + PPOB) untuk section grid homepage
        $allCategories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Promo / Artikel terbaru
        $latestArticles = Article::where('is_published', true)->orderBy('created_at', 'desc')->take(3)->get();

        // Top 5 Terlaris Gabungan (Game + PPOB)
        try {
            $topSellingCategories = \Illuminate\Support\Facades\DB::table('categories')
                ->leftJoin('products', 'products.category_id', '=', 'categories.id')
                ->leftJoin('transaction_items', 'transaction_items.product_id', '=', 'products.id')
                ->leftJoin('transactions', function ($join) {
                    $join->on('transactions.id', '=', 'transaction_items.transaction_id')
                         ->where('transactions.transaction_status', '!=', 'failed');
                })
                ->where('categories.is_active', true)
                ->groupBy('categories.id', 'categories.name', 'categories.slug', 'categories.type', 'categories.thumbnail', 'categories.icon')
                ->selectRaw("categories.id, categories.name, categories.slug, categories.type, categories.thumbnail, categories.icon, COUNT(transaction_items.id) as transaction_count, CASE WHEN categories.type IN ('game', 'pc', 'voucher') THEN 'game' ELSE 'ppob' END as segment")
                ->orderByDesc('transaction_count')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $topSellingCategories = collect();
        }

        return view('front.index', compact(
            'banners',
            'ppobPromoBanner',
            'ppobCategories',
            'ppobGrouped',
            'popularGames',
            'topSellingCategories',
            'allGames',
            'selulerGames',
            'pcGames',
            'voucherGames',
            'allCategories',
            'latestArticles',
            'searchQuery',
            'searchCategories',
            'searchProducts'
        ));
    }

    /**
     * Top Up Game landing page — all game subcategories
     */
    public function topUpGame()
    {
        $categories = Category::where('is_active', true)
            ->gameTypes()
            ->orderBy('name')
            ->get();

        $grouped = $categories->groupBy('type');

        return view('front.top-up-game', compact('categories', 'grouped'));
    }

    /**
     * PPOB landing page — all PPOB/pulsa subcategories
     */
    public function ppob()
    {
        $categories = Category::where('is_active', true)
            ->nonGameTypes()
            ->orderBy('sort_order')
            ->get();

        $grouped = $categories->groupBy('type');

        return view('front.ppob', compact('categories', 'grouped'));
    }

    public function showCategory(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$category && ctype_digit((string) $slug)) {
            $category = Category::where('id', (int) $slug)
                ->where('is_active', true)
                ->first();

            if ($category && filled($category->slug)) {
                return redirect()->route('front.category', $category->slug, 301);
            }
        }

        if (!$category) {
            abort(404);
        }

        $products = Product::with('providerMappings')
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN status_provider = 'available' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $preselectedProductId = (int) $request->query('product', 0);
        if ($preselectedProductId > 0) {
            $existsInCategory = $products->contains(fn($product) => (int) $product->id === $preselectedProductId);
            if (! $existsInCategory) {
                $preselectedProductId = 0;
            }
        }

        $paymentGateways = $this->mapPaymentGatewaysForCustomer(
            PaymentGateway::where('is_active', true)->get()
        );
        $paymentChannels = $this->buildPaymentChannelOptions($paymentGateways);
        $formFields = $category->getFormFields();

        // Calculate product groups for Modal Drill-Down
        $productGroups = $products
            ->whereNotNull('product_group')
            ->groupBy('product_group')
            ->map(function($groupItems, $groupName) {
                return [
                    'name' => $groupName,
                    'count' => $groupItems->count(),
                    // Determine semantic icon based on group name
                    'icon' => str_contains(strtolower($groupName), 'data') ? 'fas fa-globe' : 
                              (str_contains(strtolower($groupName), 'pulsa') ? 'fas fa-mobile-alt' : 
                              (str_contains(strtolower($groupName), 'driver') ? 'fas fa-motorcycle' : 'fas fa-box'))
                ];
            })
            ->values();

        // Only show modal if there's more than 1 distinct group
        $hasGroups = $productGroups->count() > 1;

        // Calculate product types for 3-level hierarchy (type → group → product)
        $productTypes = $products
            ->whereNotNull('product_type')
            ->groupBy('product_type')
            ->map(function($typeItems, $typeName) {
                // Calculate sub-groups within this type
                $subGroups = $typeItems->whereNotNull('product_group')
                    ->groupBy('product_group')
                    ->map(function($groupItems, $groupName) {
                        return [
                            'name' => $groupName,
                            'count' => $groupItems->count(),
                            'icon' => str_contains(strtolower($groupName), 'data') ? 'fas fa-globe' :
                                      (str_contains(strtolower($groupName), 'pulsa') ? 'fas fa-mobile-alt' :
                                      (str_contains(strtolower($groupName), 'driver') ? 'fas fa-motorcycle' :
                                      (str_contains(strtolower($groupName), 'nelfon') || str_contains(strtolower($groupName), 'sms') ? 'fas fa-phone' : 'fas fa-box')))
                        ];
                    })
                    ->values();

                return [
                    'name' => $typeName,
                    'count' => $typeItems->count(),
                    'subGroups' => $subGroups,
                    'hasSubGroups' => $subGroups->count() > 1,
                    'icon' => str_contains(strtolower($typeName), 'data') ? 'fas fa-globe' :
                              (str_contains(strtolower($typeName), 'pulsa') ? 'fas fa-mobile-alt' :
                              (str_contains(strtolower($typeName), 'telepon') || str_contains(strtolower($typeName), 'sms') ? 'fas fa-phone' :
                              (str_contains(strtolower($typeName), 'driver') ? 'fas fa-motorcycle' :
                              (str_contains(strtolower($typeName), 'customer') || str_contains(strtolower($typeName), 'penumpang') ? 'fas fa-user' :
                              (str_contains(strtolower($typeName), 'token') ? 'fas fa-bolt' :
                              (str_contains(strtolower($typeName), 'starlight') ? 'fas fa-star' :
                              (str_contains(strtolower($typeName), 'global') || str_contains(strtolower($typeName), 'indonesia') || str_contains(strtolower($typeName), 'malaysia') ? 'fas fa-globe-asia' : 'fas fa-box')))))))
                ];
            })
            ->values();

        $hasTypes = $productTypes->count() > 1;

        // Load approved reviews for products in this category
        $productIds = $products->pluck('id');
        $reviews = \App\Models\ProductReview::with(['user', 'product'])
            ->whereIn('product_id', $productIds)
            ->where('is_approved', true)
            ->latest()
            ->take(20)
            ->get();

        // Route to correct view based on category type
        $viewName = Category::isGameType($category->type)
            ? 'front.show-game'
            : 'front.show-ppob';

        $isPostpaid = Category::isPostpaidType($category->type);

        // Check if this game supports ID validation (nickname check)
        $supportsIdValidation = false;
        $gameValidatorInfo = null;
        if (Category::isGameType($category->type)) {
            $validator = new \App\Services\GameIdValidatorService();
            $gameValidatorInfo = $validator->detectGameFromCategory($category->name);
            $supportsIdValidation = $gameValidatorInfo !== null;
        }

        return view($viewName, compact('category', 'products', 'paymentGateways', 'paymentChannels', 'formFields', 'preselectedProductId', 'productGroups', 'hasGroups', 'productTypes', 'hasTypes', 'reviews', 'isPostpaid', 'supportsIdValidation', 'gameValidatorInfo'));
    }

    public function checkout(Request $request)
    {
        // GET /checkout has no direct page — redirect to home
        return redirect()->route('front.index');
    }

    public function cekPesanan()
    {
        return view('front.cek-pesanan');
    }

    public function prosesCekPesanan(Request $request)
    {
        $request->validate([
            'search_type' => 'required|in:invoice,target',
            'search_value' => 'required|string',
        ]);

        $query = Transaction::query();

        if ($request->search_type === 'invoice') {
            $query->where('invoice_number', $request->search_value);
        } else {
            $query->where('target', $request->search_value);
        }

        $transaction = $query->first();

        if (!$transaction) {
            return back()->with('error', 'Pesanan tidak ditemukan. Pastikan data yang dimasukkan benar.');
        }

        return view('front.cek-pesanan', compact('transaction'));
    }

    public function calculator()
    {
        return view('front.calculator');
    }

    public function page(string $slug)
    {
        $page = \App\Models\Page::where('slug', $slug)->firstOrFail();
        $contactWhatsapp = Setting::get('contact_whatsapp');
        $contactEmail = Setting::get('contact_email');

        return view('front.page', compact('slug', 'page', 'contactWhatsapp', 'contactEmail'));
    }

    private function mapPaymentGatewaysForCustomer($gateways)
    {
        return $gateways->map(function ($gateway) {
            [$displayName, $fallbackType, $methods, $channels] = $this->resolveGatewayPresentation((string) $gateway->code, (string) $gateway->name);

            $gateway->setAttribute('display_name', $displayName);
            $gateway->setAttribute('customer_methods', $methods);
            $gateway->setAttribute('customer_channels', $channels);

            if (empty($gateway->type)) {
                $gateway->setAttribute('type', $fallbackType);
            }

            return $gateway;
        });
    }

    private function resolveGatewayPresentation(string $code, string $name): array
    {
        $normalizedCode = strtolower(trim($code));

        if (in_array($normalizedCode, ['doku', 'dompetx'], true)) {
            return [
                'QRIS / E-Wallet / Virtual Account / Retail',
                'otomatis',
                ['QRIS', 'E-Wallet', 'Virtual Account', 'Alfamart/Indomaret'],
                [
                    'e_wallet' => ['DANA', 'ShopeePay', 'GoPay', 'GoPay Token', 'OVO', 'LinkAja', 'Jenius Pay', 'Sakuku', 'Doku Wallet'],
                    'transfer_bank' => ['BCA Virtual Account', 'BNI Virtual Account', 'BRI Virtual Account', 'Permata Virtual Account', 'CIMB Virtual Account'],
                    'qris' => ['QRIS'],
                    'otc_non_bank' => ['Alfamart', 'Indomaret'],
                    'perbankan_online' => ['Perbankan Online'],
                    'kartu_debit_kredit' => ['Kartu Debit / Kredit'],
                ],
            ];
        }

        if ($normalizedCode === 'midtrans') {
            return [
                'QRIS / E-Wallet / Virtual Account / Retail',
                'otomatis',
                ['QRIS', 'GoPay/ShopeePay', 'Virtual Account', 'Alfamart/Indomaret'],
                [
                    'e_wallet' => ['GoPay', 'ShopeePay'],
                    'transfer_bank' => ['BCA Virtual Account', 'BNI Virtual Account', 'BRI Virtual Account', 'Permata Virtual Account'],
                    'qris' => ['QRIS'],
                    'otc_non_bank' => ['Alfamart', 'Indomaret'],
                    'kartu_debit_kredit' => ['Kartu Debit / Kredit'],
                ],
            ];
        }

        if ($normalizedCode === 'klikqris') {
            return [
                'QRIS',
                'qris',
                ['QRIS'],
                [
                    'qris' => ['QRIS'],
                ],
            ];
        }

        return [
            $name,
            'other',
            [$name],
            [
                'other' => [$name],
            ],
        ];
    }

    private function buildPaymentChannelOptions($gateways)
    {
        $result = [];
        $seen = [];

        foreach ($gateways as $gateway) {
            $channels = (array) ($gateway->customer_channels ?? []);
            foreach ($channels as $groupKey => $items) {
                foreach ((array) $items as $channelName) {
                    $dedupeKey = strtolower($groupKey . '|' . $gateway->id . '|' . $channelName);
                    if (isset($seen[$dedupeKey])) {
                        continue;
                    }
                    $seen[$dedupeKey] = true;
                    $result[] = [
                        'group_key' => (string) $groupKey,
                        'group_label' => $this->channelGroupLabel((string) $groupKey),
                        'name' => (string) $channelName,
                        'gateway_id' => (int) $gateway->id,
                        'provider' => (string) $gateway->name,
                        'fee_flat' => (float) ($gateway->fee_flat ?? 0),
                        'fee_percent' => (float) ($gateway->fee_percent ?? 0),
                    ];
                }
            }
        }

        return collect($result);
    }

    private function channelGroupLabel(string $groupKey): string
    {
        $labels = [
            'e_wallet' => 'E-wallet',
            'transfer_bank' => 'Transfer Bank',
            'qris' => 'QRIS',
            'otc_non_bank' => 'OTC non-Bank',
            'perbankan_online' => 'Perbankan online',
            'kartu_debit_kredit' => 'Kartu Debit / Kredit',
            'voucher_fisik' => 'Voucher Fisik',
            'sms_seluler' => 'SMS & Seluler',
            'other' => 'Lainnya',
        ];

        return $labels[$groupKey] ?? ucwords(str_replace('_', ' ', $groupKey));
    }
}

