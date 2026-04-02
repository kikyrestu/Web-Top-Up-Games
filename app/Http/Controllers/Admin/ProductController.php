<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ApiProvider;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'providerMappings.apiProvider'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhereHas('category', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $products = $query->paginate(15)->withQueryString();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $providers = ApiProvider::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'providers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price_capital' => 'required|numeric|min:0',
            'price_sell' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'provider_mappings' => 'nullable|array|max:10',
            'provider_mappings.*.api_provider_id' => 'nullable|exists:api_providers,id',
            'provider_mappings.*.provider_product_code' => 'nullable|string|max:255',
            'provider_mappings.*.price_capital' => 'nullable|numeric|min:0',
            'provider_mappings.*.is_active' => 'nullable|boolean',
            'provider_mappings.*.priority' => 'nullable|integer|min:0|max:999',
        ]);

        $data = $request->except('image', 'is_active', 'provider_mappings');
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $data['image'] = ImageOptimizer::optimizeAndSave($request->file('image'), 'products', 500, 85);
        }

        DB::transaction(function () use ($data, $request) {
            $product = Product::create($data);

            $rows = collect($request->input('provider_mappings', []))
                ->filter(function ($row) {
                    return ! empty($row['api_provider_id']) && ! empty($row['provider_product_code']);
                })
                ->unique('api_provider_id')
                ->map(function ($row, $index) {
                    return [
                        'api_provider_id' => (int) $row['api_provider_id'],
                        'provider_product_code' => trim((string) $row['provider_product_code']),
                        'price_capital' => isset($row['price_capital']) && $row['price_capital'] !== '' ? (float) $row['price_capital'] : 0,
                        'is_active' => (bool) ($row['is_active'] ?? true),
                        'priority' => isset($row['priority']) && $row['priority'] !== '' ? (int) $row['priority'] : $index,
                    ];
                })
                ->values();

            if ($rows->isNotEmpty()) {
                $product->providerMappings()->createMany($rows->all());
                $product->price_capital = (float) $rows->min('price_capital');
                $product->save();
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $providers = ApiProvider::where('is_active', true)->orderBy('name')->get();
        $product->load('providerMappings');

        return view('admin.products.edit', compact('product', 'categories', 'providers'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price_capital' => 'required|numeric|min:0',
            'price_sell' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'provider_mappings' => 'nullable|array|max:10',
            'provider_mappings.*.api_provider_id' => 'nullable|exists:api_providers,id',
            'provider_mappings.*.provider_product_code' => 'nullable|string|max:255',
            'provider_mappings.*.price_capital' => 'nullable|numeric|min:0',
            'provider_mappings.*.is_active' => 'nullable|boolean',
            'provider_mappings.*.priority' => 'nullable|integer|min:0|max:999',
        ]);

        $data = $request->except('image', 'is_active', 'provider_mappings');
        $data['is_active'] = $request->has('is_active');

        DB::transaction(function () use ($request, $product, $data) {
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = ImageOptimizer::optimizeAndSave($request->file('image'), 'products', 500, 85);
            }

            $product->update($data);

            $rows = collect($request->input('provider_mappings', []))
                ->filter(function ($row) {
                    return ! empty($row['api_provider_id']) && ! empty($row['provider_product_code']);
                })
                ->unique('api_provider_id')
                ->map(function ($row, $index) {
                    return [
                        'api_provider_id' => (int) $row['api_provider_id'],
                        'provider_product_code' => trim((string) $row['provider_product_code']),
                        'price_capital' => isset($row['price_capital']) && $row['price_capital'] !== '' ? (float) $row['price_capital'] : 0,
                        'is_active' => (bool) ($row['is_active'] ?? true),
                        'priority' => isset($row['priority']) && $row['priority'] !== '' ? (int) $row['priority'] : $index,
                    ];
                })
                ->values();

            $product->providerMappings()->delete();

            if ($rows->isNotEmpty()) {
                $product->providerMappings()->createMany($rows->all());
                $product->update([
                    'price_capital' => (float) $rows->min('price_capital'),
                ]);
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function destroyBulk(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'exists:products,id',
        ]);

        $products = Product::whereIn('id', $request->selected_ids)->get();

        foreach ($products as $product) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
        }

        return redirect()->route('admin.products.index')->with('success', count($products) . ' produk berhasil dihapus secara massal.');
    }
}

