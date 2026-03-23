<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CmsBanner;
use App\Models\CmsPage;
use App\Models\Product;
use App\Models\PromoCampaign;
use App\Models\Review;
use App\Models\SeoMeta;
use Illuminate\Http\Response;
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
            'seo' => $this->seoFor('PRODUCT', (int) $product->id),
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
            'seo' => $this->seoFor('CATEGORY', (int) $category->id),
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

        $campaigns = PromoCampaign::query()
            ->where('is_active', true)
            ->where(static function ($query) use ($now): void {
                $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(static function ($query) use ($now): void {
                $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        return view('public.promo', [
            'banners' => $banners,
            'promoPages' => $promoPages,
            'campaigns' => $campaigns,
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
            'seo' => $this->seoFor('CMS_PAGE', (int) $article->id),
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

    public function sitemap(): Response
    {
        $urls = [
            [
                'loc' => route('storefront.index'),
                'changefreq' => 'hourly',
                'priority' => '1.0',
                'lastmod' => now()->toAtomString(),
            ],
            ['loc' => route('public.topup.index'), 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => now()->toAtomString()],
            ['loc' => route('public.ppob.index'), 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => now()->toAtomString()],
            ['loc' => route('public.promo'), 'changefreq' => 'daily', 'priority' => '0.8', 'lastmod' => now()->toAtomString()],
            ['loc' => route('public.articles.index'), 'changefreq' => 'daily', 'priority' => '0.8', 'lastmod' => now()->toAtomString()],
            ['loc' => route('public.reviews.index'), 'changefreq' => 'daily', 'priority' => '0.7', 'lastmod' => now()->toAtomString()],
            ['loc' => route('public.check-transaction'), 'changefreq' => 'weekly', 'priority' => '0.6', 'lastmod' => now()->toAtomString()],
        ];

        $topups = Product::query()
            ->where('is_active', true)
            ->whereRaw("UPPER(type) = 'TOPUP'")
            ->select(['slug', 'updated_at'])
            ->limit(1000)
            ->get();

        foreach ($topups as $product) {
            $urls[] = [
                'loc' => route('public.topup.show', ['gameSlug' => $product->slug]),
                'changefreq' => 'daily',
                'priority' => '0.8',
                'lastmod' => $product->updated_at?->toAtomString(),
            ];
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->whereRaw("UPPER(type) <> 'TOPUP'")
            ->select(['slug', 'updated_at'])
            ->limit(1000)
            ->get();

        foreach ($categories as $category) {
            $urls[] = [
                'loc' => route('public.ppob.show', ['categorySlug' => $category->slug]),
                'changefreq' => 'daily',
                'priority' => '0.7',
                'lastmod' => $category->updated_at?->toAtomString(),
            ];
        }

        $articles = CmsPage::query()
            ->where('is_published', true)
            ->whereRaw("UPPER(type) = 'ARTICLE'")
            ->select(['slug', 'updated_at'])
            ->limit(2000)
            ->get();

        foreach ($articles as $article) {
            $urls[] = [
                'loc' => route('public.articles.show', ['slug' => $article->slug]),
                'changefreq' => 'weekly',
                'priority' => '0.7',
                'lastmod' => $article->updated_at?->toAtomString(),
            ];
        }

        $xmlLines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $item) {
            $loc = htmlspecialchars((string) ($item['loc'] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $lastmod = htmlspecialchars((string) ($item['lastmod'] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $changefreq = htmlspecialchars((string) ($item['changefreq'] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $priority = htmlspecialchars((string) ($item['priority'] ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');

            $xmlLines[] = '<url>';
            $xmlLines[] = '<loc>'.$loc.'</loc>';

            if ($lastmod !== '') {
                $xmlLines[] = '<lastmod>'.$lastmod.'</lastmod>';
            }

            if ($changefreq !== '') {
                $xmlLines[] = '<changefreq>'.$changefreq.'</changefreq>';
            }

            if ($priority !== '') {
                $xmlLines[] = '<priority>'.$priority.'</priority>';
            }

            $xmlLines[] = '</url>';
        }

        $xmlLines[] = '</urlset>';
        $content = implode("\n", $xmlLines);

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Sitemap: '.url('/sitemap.xml'),
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    private function seoFor(string $entityType, int $entityId): array
    {
        $seo = SeoMeta::query()
            ->where('entity_type', strtoupper($entityType))
            ->where('entity_id', $entityId)
            ->first();

        return [
            'meta_title' => $seo?->meta_title,
            'meta_description' => $seo?->meta_description,
            'meta_keywords' => $seo?->meta_keywords,
            'og_title' => $seo?->og_title,
            'og_description' => $seo?->og_description,
            'og_image_path' => $seo?->og_image_path,
        ];
    }
}
