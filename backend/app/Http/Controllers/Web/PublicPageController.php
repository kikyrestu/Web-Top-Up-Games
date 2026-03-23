<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CmsBanner;
use App\Models\CmsPage;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PublicPageController extends Controller
{
    public function topUpIndex(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->whereRaw("UPPER(type) = 'TOPUP'")
            ->withCount(['products as active_products_count' => static function ($query): void {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with('category:id,name,slug')
            ->withMin(['providerPrices as lowest_price' => static function ($query): void {
                $query->where('is_active', true);
            }], 'base_price')
            ->where('is_active', true)
            ->whereRaw("UPPER(type) = 'TOPUP'")
            ->orderBy('name')
            ->limit(24)
            ->get();

        return view('public.topup.index', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    public function topUpShow(string $gameSlug): View
    {
        $product = Product::query()
            ->with(['category:id,name,slug,type', 'providerPrices.provider:id,code,name'])
            ->where('is_active', true)
            ->where('slug', $gameSlug)
            ->whereRaw("UPPER(type) = 'TOPUP'")
            ->firstOrFail();

        $reviews = Review::query()
            ->with('user:id,name')
            ->where('product_id', $product->id)
            ->where('status', 'APPROVED')
            ->orderByDesc('approved_at')
            ->limit(10)
            ->get();

        return view('public.topup.show', [
            'product' => $product,
            'reviews' => $reviews,
        ]);
    }

    public function ppobIndex(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->whereRaw("UPPER(type) <> 'TOPUP'")
            ->withCount(['products as active_products_count' => static function ($query): void {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get();

        return view('public.ppob.index', [
            'categories' => $categories,
        ]);
    }

    public function ppobShow(string $categorySlug): View
    {
        $category = Category::query()
            ->where('is_active', true)
            ->whereRaw("UPPER(type) <> 'TOPUP'")
            ->where('slug', $categorySlug)
            ->firstOrFail();

        $products = Product::query()
            ->withMin(['providerPrices as lowest_price' => static function ($query): void {
                $query->where('is_active', true);
            }], 'base_price')
            ->where('is_active', true)
            ->where('category_id', $category->id)
            ->orderBy('name')
            ->get();

        return view('public.ppob.show', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    public function promo(): View
    {
        $now = now();

        $banners = CmsBanner::query()
            ->where('is_active', true)
            ->where(static function ($query) use ($now): void {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(static function ($query) use ($now): void {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        $promoPages = CmsPage::query()
            ->where('is_published', true)
            ->whereRaw("UPPER(type) = 'PROMO'")
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        return view('public.promo', [
            'banners' => $banners,
            'promoPages' => $promoPages,
        ]);
    }

    public function articleIndex(): View
    {
        $articles = CmsPage::query()
            ->where('is_published', true)
            ->whereRaw("UPPER(type) = 'ARTICLE'")
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('public.articles.index', [
            'articles' => $articles,
        ]);
    }

    public function articleShow(string $slug): View
    {
        $article = CmsPage::query()
            ->where('is_published', true)
            ->whereRaw("UPPER(type) = 'ARTICLE'")
            ->where('slug', $slug)
            ->firstOrFail();

        $latestArticles = CmsPage::query()
            ->where('is_published', true)
            ->whereRaw("UPPER(type) = 'ARTICLE'")
            ->where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('public.articles.show', [
            'article' => $article,
            'latestArticles' => $latestArticles,
        ]);
    }

    public function reviewIndex(): View
    {
        $reviews = Review::query()
            ->with(['product:id,name,slug', 'user:id,name'])
            ->where('status', 'APPROVED')
            ->orderByDesc('approved_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $ratingStats = [
            'total' => Review::query()->where('status', 'APPROVED')->count(),
            'avg' => round((float) Review::query()->where('status', 'APPROVED')->avg('rating'), 2),
            'five_star' => Review::query()->where('status', 'APPROVED')->where('rating', 5)->count(),
        ];

        return view('public.reviews.index', [
            'reviews' => $reviews,
            'ratingStats' => $ratingStats,
        ]);
    }

    public function checkTransaction(): View
    {
        return view('public.check-transaction');
    }

    public function handleCheckTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_code' => ['required', 'string', 'max:50'],
        ]);

        return redirect()->route('storefront.track', [
            'orderCode' => $validated['order_code'],
        ]);
    }
}
