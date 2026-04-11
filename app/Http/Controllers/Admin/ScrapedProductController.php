<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiProvider;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductProviderMapping;
use App\Models\ScrapedProduct;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScrapedProductController extends Controller
{
    public function index(Request $request)
    {
        $query = ScrapedProduct::with('apiProvider');

        // Filter by provider
        if ($request->filled('provider_id')) {
            $query->where('api_provider_id', $request->provider_id);
        }

        // Filter by brand
        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by import status
        if ($request->filled('status')) {
            if ($request->status === 'imported') {
                $query->where('is_imported', true);
            } elseif ($request->status === 'new') {
                $query->where('is_imported', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('provider_product_code', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $scrapedProducts = $query->orderBy('brand')->orderBy('price')->paginate(50)->appends($request->query());

        // Get filter options
        $providers = ApiProvider::where('is_active', true)->orderBy('name')->get();
        $brands    = ScrapedProduct::select('brand')->distinct()->whereNotNull('brand')->orderBy('brand')->pluck('brand');
        $types     = ['prepaid', 'pasca'];

        // Stats
        $stats = [
            'total'     => ScrapedProduct::count(),
            'imported'  => ScrapedProduct::where('is_imported', true)->count(),
            'new'       => ScrapedProduct::where('is_imported', false)->count(),
            'available' => ScrapedProduct::where('status_provider', 'available')->count(),
        ];

        return view('admin.scraped_products.index', compact(
            'scrapedProducts', 'providers', 'brands', 'types', 'stats'
        ));
    }

    /**
     * Batch import selected scraped products into the main products table.
     */
    public function import(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:scraped_products,id',
        ]);

        $scrapedItems = ScrapedProduct::with('apiProvider')
            ->whereIn('id', $request->ids)
            ->where('is_imported', false)
            ->get();

        if ($scrapedItems->isEmpty()) {
            return back()->with('error', 'Tidak ada produk baru yang bisa diimport.');
        }

        $pricingMode  = Setting::get('pricing_mode', 'manual');
        $importedCount = 0;

        DB::transaction(function () use ($scrapedItems, $pricingMode, &$importedCount) {
            foreach ($scrapedItems as $scraped) {
                // Smart Mapping Dictionary to determine Category 'type'
                $brandString = strtolower($scraped->brand ?: $scraped->category_name ?: 'lainnya');
                $categoryNameLower = strtolower($scraped->category_name ?? '');
                $matchedType = 'ppob'; // Default

                // Check category_name first — most reliable indicator from scraped data
                if (in_array($categoryNameLower, ['games', 'game'])) {
                    $matchedType = 'game';
                } elseif (str_contains($brandString, 'dana') || str_contains($brandString, 'ovo') || str_contains($brandString, 'gopay') || str_contains($brandString, 'shopee') || str_contains($brandString, 'linkaja') || str_contains($brandString, 'e-money') || str_contains($brandString, 'e-toll') || str_contains($brandString, 'maxim') || str_contains($brandString, 'grab') || str_contains($brandString, 'gojek') || str_contains($brandString, 'isaku') || $categoryNameLower === 'e-money') {
                    $matchedType = 'e-money';
                } elseif (str_contains($brandString, 'telkomsel') || str_contains($brandString, 'indosat') || str_contains($brandString, 'xl') || str_contains($brandString, 'axis') || str_contains($brandString, 'tri') || str_contains($brandString, 'smartfren') || str_contains($brandString, 'pulsa') || str_contains($brandString, 'data') || in_array($categoryNameLower, ['pulsa', 'data', 'masa aktif', 'paket sms & telpon', 'aktivasi perdana', 'aktivasi voucher'])) {
                    $matchedType = 'pulsa';
                } elseif (str_contains($brandString, 'pln') || str_contains($brandString, 'bpjs') || str_contains($brandString, 'pdam') || str_contains($brandString, 'token') || str_contains($brandString, 'tagihan') || in_array($categoryNameLower, ['pln', 'pascabayar'])) {
                    $matchedType = 'tagihan';
                } elseif (str_contains($brandString, 'mobile legends') || str_contains($brandString, 'free fire') || str_contains($brandString, 'pubg') || str_contains($brandString, 'genshin') || str_contains($brandString, 'undawn') || str_contains($brandString, 'diamond') || str_contains($brandString, 'valorant') || str_contains($brandString, 'game')) {
                    $matchedType = 'game';
                } elseif (str_contains($brandString, 'voucher') && !str_contains($brandString, 'tv') && !str_contains($brandString, 'vision') || $categoryNameLower === 'voucher') {
                    $matchedType = 'voucher';
                } elseif (str_contains($brandString, 'tv') || str_contains($brandString, 'vision') || str_contains($brandString, 'nex') || str_contains($brandString, 'k-vision') || $categoryNameLower === 'tv') {
                    $matchedType = 'tv';
                } elseif ($scraped->type === 'pasca' || $scraped->type === 'postpaid') {
                    $matchedType = 'tagihan';
                } elseif ($categoryNameLower === 'gas') {
                    $matchedType = 'ppob';
                }

                $originalBrand = $scraped->brand ?: 'Lainnya';
                $finalCategoryName = strtoupper($originalBrand);

                if (strtolower($finalCategoryName) === 'k-vision dan gol') {
                    $finalCategoryName = 'K-VISION dan GOL';
                }

                // Find or create category based on brand
                $category = Category::firstOrCreate(
                    ['name' => $finalCategoryName],
                    [
                        'slug'         => Str::slug($finalCategoryName),
                        'is_active'    => true,
                        'sort_order'   => 99,
                        'type'         => $matchedType,
                        'input_fields' => Category::detectInputFields($matchedType, $finalCategoryName),
                    ]
                );

                // Check if product with same name in same category already exists
                $product = Product::where('category_id', $category->id)
                    ->where('name', $scraped->product_name)
                    ->first();

                $capitalPrice = (float) $scraped->price;
                $sellPrice    = $pricingMode === 'cheapest_auto'
                    ? \App\Models\Product::calculateSuggestedPrice($capitalPrice, $category->type)
                    : (\App\Models\Category::isGameType($category->type) ? round($capitalPrice * 1.1, 2) : $capitalPrice);

                $productGroup = $this->detectProductGroup(
                    $scraped->brand ?? '',
                    $scraped->product_name,
                    $scraped->category_name ?? ''
                );

                $productType = $this->detectProductType(
                    $scraped->brand ?? '',
                    $scraped->product_name,
                    $scraped->category_name ?? '',
                    $productGroup
                );

                if (!$product) {
                    $product = Product::create([
                        'category_id'    => $category->id,
                        'name'           => $scraped->product_name,
                        'product_type'   => $productType,
                        'product_group'  => $productGroup,
                        'price_capital'  => $capitalPrice,
                        'price_sell'     => $sellPrice,
                        'is_active'      => true,
                        'status_provider' => $scraped->status_provider ?? 'available',
                    ]);
                } else {
                    $updates = [];
                    // Update product_group if still null (backfill on re-import)
                    if (is_null($product->product_group) && $productGroup) {
                        $updates['product_group'] = $productGroup;
                    }
                    if (is_null($product->product_type) && $productType) {
                        $updates['product_type'] = $productType;
                    }
                    if (!empty($updates)) {
                        $product->update($updates);
                    }
                }

                // Create provider mapping
                ProductProviderMapping::updateOrCreate(
                    [
                        'product_id'      => $product->id,
                        'api_provider_id' => $scraped->api_provider_id,
                    ],
                    [
                        'provider_product_code' => $scraped->provider_product_code,
                        'price_capital'         => $capitalPrice,
                        'is_active'             => true,
                        'priority'              => 0,
                    ]
                );

                // Update cheapest price_capital on product
                $cheapest = ProductProviderMapping::where('product_id', $product->id)
                    ->where('is_active', true)
                    ->min('price_capital');

                if ($cheapest !== null) {
                    $product->update(['price_capital' => $cheapest]);

                    if ($pricingMode === 'cheapest_auto') {
                        $cheapestFloat = (float) $cheapest;
                        $product->update([
                            'price_sell' => round($cheapestFloat + $product->calculateMarkup($cheapestFloat), 2),
                        ]);
                    }
                }

                // Mark as imported
                $scraped->update([
                    'is_imported'         => true,
                    'imported_product_id' => $product->id,
                ]);

                $importedCount++;
            }
        });

        return back()->with('success', "Berhasil import {$importedCount} produk ke database.");
    }

    /**
     * Detect product type (parent grouping level) based on brand, product name, and sub-group.
     * This creates a 2-level hierarchy: product_type → product_group → products
     */
    private function detectProductType(string $brand, string $productName, string $categoryName, ?string $productGroup): ?string
    {
        $brandStr = strtolower(trim($brand ?: $categoryName));
        $nameStr  = strtolower(trim($productName));

        // === OPERATOR SELULER (Telkomsel, Indosat, XL, Axis, Tri, Smartfren) ===
        $operators = ['telkomsel', 'indosat', 'isat', 'xl', 'axis', 'tri', 'three', 'kartu3', 'smartfren', 'smart', 'by.u'];
        if (in_array($brandStr, $operators)) {
            // Pulsa Reguler / Masa Aktif → "Pulsa"
            if (in_array($productGroup, ['Pulsa Reguler', 'Masa Aktif'])) {
                return 'Pulsa';
            }
            // Paket Nelfon / SMS → "Paket Telepon & SMS"
            if (in_array($productGroup, ['Paket Nelfon', 'Paket SMS'])) {
                return 'Paket Telepon & SMS';
            }
            // Everything data-related → "Paket Data"
            if ($productGroup !== null) {
                return 'Paket Data';
            }
            // Fallback detection from product name
            if (str_contains($nameStr, 'pulsa') || preg_match('/^(telkomsel|indosat|xl|axis|tri|smartfren)\s+\d+\.?\d*k?$/i', $nameStr)) {
                return 'Pulsa';
            }
            if (str_contains($nameStr, 'nelfon') || str_contains($nameStr, 'telepon') || str_contains($nameStr, 'sms')) {
                return 'Paket Telepon & SMS';
            }
            return 'Paket Data';
        }

        // === E-MONEY / E-WALLET (Gopay, OVO, Dana, ShopeePay, LinkAja, Grab, Maxim, iSaku) ===
        $emoneyBrands = ['gopay', 'gojek', 'ovo', 'dana', 'shopeepay', 'shopee', 'linkaja', 'grab', 'maxim', 'isaku', 'e-toll'];
        if (in_array($brandStr, $emoneyBrands) || str_contains($brandStr, 'e-money') || str_contains($brandStr, 'e-wallet')) {
            // Use existing product_group as the type (Driver, Customer, etc.)
            if ($productGroup) {
                return $productGroup;
            }
            return null;
        }

        // === TV / K-VISION ===
        if (str_contains($brandStr, 'k-vision') || str_contains($brandStr, 'kvision') || str_contains($brandStr, 'gol') || str_contains($brandStr, 'tv') || str_contains($brandStr, 'nex')) {
            if ($productGroup) {
                return $productGroup;
            }
            return null;
        }

        // === GAMES — all game brands use product_group as the type ===
        $gameBrands = $this->getKnownGameBrands();
        if (in_array($brandStr, $gameBrands)
            || str_contains($brandStr, 'game')
            || str_contains($brandStr, 'diamond')
            || in_array($categoryName ? strtolower($categoryName) : '', ['game', 'games', 'game online', 'voucher game', 'diamond'])) {
            if ($productGroup) {
                return $productGroup;
            }
            return null;
        }

        // === TAGIHAN (PLN, BPJS, PDAM) ===
        if (str_contains($brandStr, 'pln')) {
            if (str_contains($nameStr, 'token') || str_contains($nameStr, 'prepaid')) return 'Token Listrik';
            if (str_contains($nameStr, 'pascabayar') || str_contains($nameStr, 'tagihan')) return 'Pascabayar';
            return null;
        }

        // Default: no type
        return null;
    }

    /**
     * Detect sub-group name based on brand and product name.
     */
    private function detectProductGroup(string $brand, string $productName, string $categoryName): ?string
    {
        $brandStr = strtolower(trim($brand ?: $categoryName));
        $nameStr  = strtolower(trim($productName));

        // Format Helper
        $format = function($prefix, $suffix) {
            return trim($prefix . ' ' . $suffix);
        };

        if ($brandStr === 'telkomsel') {
            if (str_contains($nameStr, 'flash')) return 'Data Flash';
            if (str_contains($nameStr, 'combo sakti')) return 'Data Combo Sakti';
            if (str_contains($nameStr, 'combo')) return 'Data Combo';
            if (str_contains($nameStr, 'internetmax lite')) return 'Data InternetMax Lite';
            if (str_contains($nameStr, 'internetmax prime')) return 'Data InternetMax Prime';
            if (str_contains($nameStr, 'internetmax')) return 'Data InternetMAX';
            if (str_contains($nameStr, 'surprise')) return 'Data Surprise Deal';
            if (str_contains($nameStr, 'super seru')) return 'Data Super Seru';
            if (str_contains($nameStr, 'nelfon') || str_contains($nameStr, 'telepon')) return 'Paket Nelfon';
            if (str_contains($nameStr, 'sms')) return 'Paket SMS';
            if (str_contains($nameStr, 'masa aktif')) return 'Masa Aktif';
            return 'Pulsa Reguler';
        }

        if ($brandStr === 'indosat' || $brandStr === 'isat') {
            if (str_contains($nameStr, 'freedom internet')) return 'Freedom Internet';
            if (str_contains($nameStr, 'freedom u')) return 'Freedom U';
            if (str_contains($nameStr, 'freedom combo')) return 'Freedom Combo';
            if (str_contains($nameStr, 'yellow')) return 'Yellow';
            if (str_contains($nameStr, 'data') || str_contains($nameStr, 'kuota')) return 'Paket Data';
            if (str_contains($nameStr, 'nelfon') || str_contains($nameStr, 'telepon')) return 'Paket Nelfon';
            if (str_contains($nameStr, 'sms')) return 'Paket SMS';
            if (str_contains($nameStr, 'masa aktif')) return 'Masa Aktif';
            return 'Pulsa Reguler';
        }

        if ($brandStr === 'xl') {
            if (str_contains($nameStr, 'xtra combo flex')) return 'Xtra Combo Flex';
            if (str_contains($nameStr, 'xtra combo')) return 'Xtra Combo';
            if (str_contains($nameStr, 'xtra on')) return 'Xtra On';
            if (str_contains($nameStr, 'data') || str_contains($nameStr, 'kuota') || str_contains($nameStr, 'hotrod')) return 'Paket Data';
            if (str_contains($nameStr, 'nelfon') || str_contains($nameStr, 'telepon')) return 'Paket Nelfon';
            if (str_contains($nameStr, 'sms')) return 'Paket SMS';
            if (str_contains($nameStr, 'masa aktif')) return 'Masa Aktif';
            return 'Pulsa Reguler';
        }

        if ($brandStr === 'axis') {
            if (str_contains($nameStr, 'bronet')) return 'Bronet';
            if (str_contains($nameStr, 'aigo')) return 'Aigo';
            if (str_contains($nameStr, 'owsem')) return 'Owsem';
            if (str_contains($nameStr, 'data') || str_contains($nameStr, 'kuota')) return 'Paket Data';
            if (str_contains($nameStr, 'nelfon') || str_contains($nameStr, 'telepon')) return 'Paket Nelfon';
            if (str_contains($nameStr, 'sms')) return 'Paket SMS';
            if (str_contains($nameStr, 'masa aktif')) return 'Masa Aktif';
            return 'Pulsa Reguler';
        }

        if ($brandStr === 'tri' || $brandStr === 'three' || $brandStr === 'kartu3') {
            if (str_contains($nameStr, 'happy')) return 'Happy';
            if (str_contains($nameStr, 'alwayson') || str_contains($nameStr, 'aon')) return 'AlwaysOn';
            if (str_contains($nameStr, 'kuota') || str_contains($nameStr, 'data')) return 'Paket Data';
            if (str_contains($nameStr, 'nelfon') || str_contains($nameStr, 'telepon')) return 'Paket Nelfon';
            if (str_contains($nameStr, 'sms')) return 'Paket SMS';
            if (str_contains($nameStr, 'masa aktif')) return 'Masa Aktif';
            return 'Pulsa Reguler';
        }

        if ($brandStr === 'smartfren' || $brandStr === 'smart') {
            if (str_contains($nameStr, 'unlimited')) return 'Unlimited';
            if (str_contains($nameStr, 'kuota') || str_contains($nameStr, 'data') || str_contains($nameStr, 'volume')) return 'Paket Data';
            if (str_contains($nameStr, 'nelfon') || str_contains($nameStr, 'telepon')) return 'Paket Nelfon';
            if (str_contains($nameStr, 'sms')) return 'Paket SMS';
            if (str_contains($nameStr, 'masa aktif')) return 'Masa Aktif';
            return 'Pulsa Reguler';
        }

        if ($brandStr === 'maxim') {
            if (str_contains($nameStr, 'driver')) return 'Maxim Driver';
            if (str_contains($nameStr, 'customer') || str_contains($nameStr, 'penumpang')) return 'Maxim Customer';
            return 'Maxim';
        }

        if ($brandStr === 'grab') {
            if (str_contains($nameStr, 'driver')) return 'Grab Driver';
            if (str_contains($nameStr, 'penumpang')) return 'Grab Penumpang';
            return 'Grab';
        }

        if ($brandStr === 'gojek' || $brandStr === 'gopay') {
            if (str_contains($nameStr, 'driver')) return 'Gojek Driver';
            if (str_contains($nameStr, 'customer') || str_contains($nameStr, 'penumpang')) return 'Gopay Customer';
            return 'Gopay';
        }

        if (str_contains($brandStr, 'k-vision') || str_contains($brandStr, 'kvision') || str_contains($brandStr, 'gol')) {
            if (str_contains($nameStr, 'gol')) return 'K-Vision GOL';
            if (str_contains($nameStr, 'bromo')) return 'K-Vision Bromo';
            if (str_contains($nameStr, 'cartenz')) return 'K-Vision Cartenz';
            
            // Extract Paket Name (e.g. "K-Vision Paket Juara")
            if (preg_match('/(paket\s+[a-z0-9]+)/i', $nameStr, $matches)) {
                return 'K-Vision ' . ucwords($matches[1]);
            }
            return 'K-Vision';
        }

        if ($brandStr === 'mobile legends') {
            if (str_contains($nameStr, 'starlight')) return 'ML Starlight';
            if (str_contains($nameStr, 'twilight')) return 'ML Twilight';
            if (str_contains($nameStr, 'weekly')) return 'ML Weekly Pass';
            $region = $this->detectGameRegion($productName);
            return 'ML ' . ($region ?? 'Indonesia');
        }

        if ($brandStr === 'free fire' || $brandStr === 'free fire max') {
            if (str_contains($nameStr, 'member')) return 'FF Membership';
            $region = $this->detectGameRegion($productName);
            return 'FF ' . ($region ?? 'Indonesia');
        }

        if ($brandStr === 'pubg mobile' || $brandStr === 'pubg' || $brandStr === 'pubg mobile lite') {
            if (str_contains($nameStr, 'royale pass') || str_contains($nameStr, 'elite pass')) return 'PUBG Pass';
            $region = $this->detectGameRegion($productName);
            return 'PUBG ' . ($region ?? 'Indonesia');
        }

        // === GENERIC GAME GROUPING ===
        // For all other game brands, detect region/server and special product types
        if (in_array($categoryName ? strtolower($categoryName) : '', ['game', 'games', 'game online', 'voucher game', 'diamond'])
            || in_array($brandStr, $this->getKnownGameBrands())) {

            $special = $this->detectGameSpecialCategory($nameStr);
            if ($special) return $special;

            $region = $this->detectGameRegion($productName);
            if ($region) return $region;

            // Check for Garena in name (common game publisher)
            if (str_contains($nameStr, 'garena')) {
                if (preg_match('/garena\s*\((my)\)/i', $productName)) return 'Garena MY';
                return 'Garena';
            }

            return 'Indonesia';
        }

        // Default: No grouping
        return null;
    }

    /**
     * Detect server region from game product name.
     */
    private function detectGameRegion(string $productName): ?string
    {
        // Parenthesized country codes: (ID), (MY), (PH), (SG), (TH), (TW), (USA), etc.
        $codeMap = [
            'id' => 'Indonesia', 'indonesia' => 'Indonesia',
            'my' => 'Malaysia', 'malaysia' => 'Malaysia',
            'ph' => 'Philippines', 'filipina' => 'Philippines', 'philippines' => 'Philippines',
            'sg' => 'Singapore', 'singapore' => 'Singapore', 'singapura' => 'Singapore',
            'th' => 'Thailand', 'thailand' => 'Thailand',
            'tw' => 'Taiwan', 'taiwan' => 'Taiwan',
            'us' => 'USA', 'usa' => 'USA', 'america' => 'USA',
            'br' => 'Brazil', 'brazil' => 'Brazil',
            'ru' => 'Russia', 'russia' => 'Russia',
            'tr' => 'Turkey', 'turkey' => 'Turkey',
            'vn' => 'Vietnam', 'vietnam' => 'Vietnam',
            'jp' => 'Japan', 'japan' => 'Japan',
            'kr' => 'Korea', 'korea' => 'Korea',
            'cn' => 'China', 'china' => 'China',
            'in' => 'India', 'india' => 'India',
            'global' => 'Global',
            'sea' => 'SEA',
        ];

        // Match (CODE) pattern at end or within name
        if (preg_match('/\(([a-z]+)\)\s*$/i', $productName, $m)) {
            $code = strtolower($m[1]);
            if (isset($codeMap[$code])) return $codeMap[$code];
        }

        // Match region keywords in name (without parentheses)
        $name = strtolower($productName);
        $regionKeywords = [
            'global' => 'Global',
            'malaysia' => 'Malaysia',
            'philippines' => 'Philippines', 'filipina' => 'Philippines',
            'singapore' => 'Singapore', 'singapura' => 'Singapore',
            'thailand' => 'Thailand',
            'taiwan' => 'Taiwan',
            'brazil' => 'Brazil',
            'russia' => 'Russia',
            'turkey' => 'Turkey',
            'vietnam' => 'Vietnam',
            'bangladesh' => 'Bangladesh',
            'japan' => 'Japan',
            'korea' => 'Korea',
            'latin america' => 'Latin America',
            'garena (my)' => 'Garena MY',
        ];

        foreach ($regionKeywords as $keyword => $region) {
            if (str_contains($name, $keyword)) return $region;
        }

        return null;
    }

    /**
     * Detect special product category for games (membership, pass, etc.)
     */
    private function detectGameSpecialCategory(string $nameLower): ?string
    {
        $specials = [
            'weekly diamond pass' => 'Weekly Pass',
            'weekly pass' => 'Weekly Pass',
            'weekly elite bundle' => 'Weekly Pass',
            'weekly deal pack' => 'Weekly Pack',
            'weekly membership' => 'Membership',
            'monthly card' => 'Monthly Card',
            'monthly membership' => 'Membership',
            'membership bulanan' => 'Membership',
            'membership mingguan' => 'Membership',
            'member' => 'Membership',
            'starlight' => 'Starlight',
            'twilight' => 'Twilight',
            'elite pass' => 'Elite Pass',
            'royale pass' => 'Royale Pass',
            'battle pass' => 'Battle Pass',
            'express supply pass' => 'Express Supply Pass',
            'adventure pack' => 'Special Pack',
            'genesis supplies' => 'Special Pack',
            'first recharge' => 'First Recharge',
            'cek username' => 'Cek Username',
        ];

        foreach ($specials as $keyword => $category) {
            if (str_contains($nameLower, $keyword)) return $category;
        }

        return null;
    }

    /**
     * Known game brand names (lowercase) for generic game detection.
     */
    private function getKnownGameBrands(): array
    {
        return [
            'mobile legends', 'free fire', 'free fire max', 'pubg mobile', 'pubg', 'pubg mobile lite',
            'genshin impact', 'honkai star rail', 'honkai impact 3', 'honkai impact',
            'valorant', 'league of legends', 'league of legends wild rift',
            'call of duty mobile', 'call of duty', 'arena of valor',
            'tower of fantasy', 'zenless zone zero',
            'delta force', 'crossfire', 'crossfire legends', 'point blank',
            'magic chess', 'marvel rivals', 'stumble guys',
            'ragnarok m', 'ragnarok origin', 'ragnarok',
            'undawn', 'identity v', 'onmyoji arena',
            'afk journey', 'age of empires mobile',
            'arena breakout', 'aether gazer', 'asphalt 9',
            'blood strike', 'captain tsubasa ace', 'culinary tour',
            'destiny m', 'draconia saga', 'dragonheir silent gods',
            'eggy party', 'fc mobile', 'garena',
            'ghost story', 'growtopia', 'guns of glory',
            'harry potter', 'heaven burns red', 'heroes evolved',
            'honor of kings', 'isekai feast', 'kings choice',
            'laplace m', 'lifeafter', 'lords mobile',
            'melojam', 'metal slug', 'mob rush',
            'mobile legends adventure', 'moonlight blade',
            'mu origin', 'naruto', 'nba infinite',
            'octopath traveler', 'one punch man', 'pixel gun',
            'pokemon unite', 'punishing gray raven',
            'sausage man', 'seal m', 'smash legends',
            'snowbreak', 'soul land', 'speed drifters',
            'state of survival', 'super sus', 'teamfight tactics',
            'the ants', 'tom and jerry', 'watcher of realms',
            'werewolf', 'whiteout survival', 'world war heroes', 'zepeto',
        ];
    }
}
