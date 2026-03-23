<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CmsBanner;
use App\Models\CmsPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

final class AdminCmsController extends Controller
{
    public function pagesIndex(Request $request): View
    {
        $type = strtoupper(trim((string) $request->query('type', '')));
        $search = trim((string) $request->query('q', ''));

        $pages = CmsPage::query()
            ->when($type !== '', static fn ($query) => $query->whereRaw('UPPER(type) = ?', [$type]))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cms.pages.index', [
            'pages' => $pages,
            'filters' => [
                'type' => $type,
                'q' => $search,
            ],
        ]);
    }

    public function pagesCreate(): View
    {
        return view('admin.cms.pages.form', [
            'page' => new CmsPage(),
            'formMode' => 'create',
        ]);
    }

    public function pagesStore(Request $request): RedirectResponse
    {
        $data = $this->validatePage($request);

        CmsPage::query()->create($data);

        return redirect()->route('admin.cms.pages.index')->with('notice', 'Halaman CMS berhasil dibuat.');
    }

    public function pagesEdit(CmsPage $page): View
    {
        return view('admin.cms.pages.form', [
            'page' => $page,
            'formMode' => 'edit',
        ]);
    }

    public function pagesUpdate(Request $request, CmsPage $page): RedirectResponse
    {
        $data = $this->validatePage($request, $page->id);
        $page->update($data);

        return redirect()->route('admin.cms.pages.index')->with('notice', 'Halaman CMS berhasil diperbarui.');
    }

    public function pagesDestroy(CmsPage $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.cms.pages.index')->with('notice', 'Halaman CMS berhasil dihapus.');
    }

    public function bannersIndex(Request $request): View
    {
        $position = strtoupper(trim((string) $request->query('position', '')));
        $search = trim((string) $request->query('q', ''));

        $banners = CmsBanner::query()
            ->when($position !== '', static fn ($query) => $query->whereRaw('UPPER(position) = ?', [$position]))
            ->when($search !== '', static fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.cms.banners.index', [
            'banners' => $banners,
            'filters' => [
                'position' => $position,
                'q' => $search,
            ],
        ]);
    }

    public function bannersCreate(): View
    {
        return view('admin.cms.banners.form', [
            'banner' => new CmsBanner(),
            'formMode' => 'create',
        ]);
    }

    public function bannersStore(Request $request): RedirectResponse
    {
        $data = $this->validateBanner($request);
        CmsBanner::query()->create($data);

        return redirect()->route('admin.cms.banners.index')->with('notice', 'Banner berhasil dibuat.');
    }

    public function bannersEdit(CmsBanner $banner): View
    {
        return view('admin.cms.banners.form', [
            'banner' => $banner,
            'formMode' => 'edit',
        ]);
    }

    public function bannersUpdate(Request $request, CmsBanner $banner): RedirectResponse
    {
        $data = $this->validateBanner($request);
        $banner->update($data);

        return redirect()->route('admin.cms.banners.index')->with('notice', 'Banner berhasil diperbarui.');
    }

    public function bannersDestroy(CmsBanner $banner): RedirectResponse
    {
        $banner->delete();

        return redirect()->route('admin.cms.banners.index')->with('notice', 'Banner berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePage(Request $request, ?int $pageId = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('cms_pages', 'slug')->ignore($pageId),
            ],
            'type' => ['required', 'string', Rule::in(['PAGE', 'ARTICLE', 'PROMO'])],
            'content' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $title = (string) $validated['title'];
        $slugInput = trim((string) ($validated['slug'] ?? ''));
        $slug = $slugInput !== '' ? Str::slug($slugInput) : Str::slug($title);

        $validated['slug'] = $slug !== '' ? $slug : 'page-'.Str::lower(Str::random(8));
        $validated['is_published'] = $request->boolean('is_published');

        if ($validated['is_published'] === true && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        if ($validated['is_published'] === false) {
            $validated['published_at'] = null;
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateBanner(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:30'],
            'image_path' => ['required', 'string', 'max:255'],
            'target_url' => ['nullable', 'string', 'max:1024'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['position'] = strtoupper((string) $validated['position']);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
