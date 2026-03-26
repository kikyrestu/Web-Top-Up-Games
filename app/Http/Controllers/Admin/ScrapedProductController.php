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

        $markupPct    = (float) Setting::get('markup_percentage', 5);
        $pricingMode  = Setting::get('pricing_mode', 'manual');
        $importedCount = 0;

        DB::transaction(function () use ($scrapedItems, $markupPct, $pricingMode, &$importedCount) {
            foreach ($scrapedItems as $scraped) {
                // Find or create category based on brand
                $category = Category::firstOrCreate(
                    ['name' => $scraped->brand ?: 'Lainnya'],
                    [
                        'slug'       => Str::slug($scraped->brand ?: 'lainnya'),
                        'is_active'  => true,
                        'sort_order' => 99,
                        'type'       => $scraped->type === 'pasca' ? 'ppob' : 'seluler',
                    ]
                );

                // Check if product with same name in same category already exists
                $product = Product::where('category_id', $category->id)
                    ->where('name', $scraped->product_name)
                    ->first();

                $capitalPrice = (float) $scraped->price;
                $sellPrice    = $pricingMode === 'cheapest_auto'
                    ? round($capitalPrice + (($capitalPrice * $markupPct) / 100), 2)
                    : round($capitalPrice * 1.1, 2); // 10% default margin for manual

                if (!$product) {
                    $product = Product::create([
                        'category_id'  => $category->id,
                        'name'         => $scraped->product_name,
                        'price_capital' => $capitalPrice,
                        'price_sell'    => $sellPrice,
                        'is_active'     => $scraped->status_provider === 'available',
                    ]);
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
                        $product->update([
                            'price_sell' => round($cheapest + (($cheapest * $markupPct) / 100), 2),
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
}
