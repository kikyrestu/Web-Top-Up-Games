<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CatalogController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        $type = strtoupper(trim((string) $request->query('type', '')));

        $categories = Category::query()
            ->where('is_active', true)
            ->when($type !== '', static fn ($query) => $query->whereRaw('UPPER(type) = ?', [$type]))
            ->withCount(['products as active_products_count' => static function ($query): void {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'type', 'is_active']);

        return response()->json([
            'success' => true,
            'code' => 'CATALOG_CATEGORIES_FOUND',
            'message' => 'Catalog categories loaded',
            'data' => [
                'items' => $categories->map(static fn (Category $category): array => [
                    'id' => (int) $category->id,
                    'name' => (string) $category->name,
                    'slug' => (string) $category->slug,
                    'type' => (string) $category->type,
                    'active_products_count' => (int) ($category->active_products_count ?? 0),
                ])->values(),
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'max:40'],
            'category_slug' => ['nullable', 'string', 'max:120'],
            'q' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $type = strtoupper(trim((string) ($validated['type'] ?? '')));
        $categorySlug = trim((string) ($validated['category_slug'] ?? ''));
        $search = trim((string) ($validated['q'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 24);

        $products = Product::query()
            ->with('category:id,name,slug,type')
            ->withMin(['providerPrices as lowest_base_price' => static function ($query): void {
                $query->where('is_active', true);
            }], 'base_price')
            ->where('is_active', true)
            ->when($type !== '', static fn ($query) => $query->whereRaw('UPPER(type) = ?', [$type]))
            ->when($categorySlug !== '', static fn ($query) => $query->whereHas('category', static fn ($catQuery) => $catQuery->where('slug', $categorySlug)))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'code' => 'CATALOG_PRODUCTS_FOUND',
            'message' => 'Catalog products loaded',
            'data' => [
                'items' => $products->getCollection()->map(static fn (Product $product): array => [
                    'id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'slug' => (string) $product->slug,
                    'sku' => (string) $product->sku,
                    'type' => (string) $product->type,
                    'lowest_base_price' => $product->lowest_base_price !== null ? (float) $product->lowest_base_price : null,
                    'category' => [
                        'id' => (int) ($product->category?->id ?? 0),
                        'name' => (string) ($product->category?->name ?? ''),
                        'slug' => (string) ($product->category?->slug ?? ''),
                        'type' => (string) ($product->category?->type ?? ''),
                    ],
                ])->values(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'per_page' => $products->perPage(),
                    'last_page' => $products->lastPage(),
                    'total' => $products->total(),
                ],
            ],
        ]);
    }
}
