<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CmsPage;
use App\Models\Product;
use App\Models\SeoMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

final class AdminSeoController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const ENTITY_TYPES = ['CMS_PAGE', 'PRODUCT', 'CATEGORY'];

    public function index(Request $request): View
    {
        $entityType = strtoupper(trim((string) $request->query('entity_type', '')));
        $search = trim((string) $request->query('q', ''));

        $rows = SeoMeta::query()
            ->when($entityType !== '', static fn ($query) => $query->where('entity_type', $entityType))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('meta_title', 'like', '%'.$search.'%')
                        ->orWhere('og_title', 'like', '%'.$search.'%')
                        ->orWhere('entity_id', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.seo.index', [
            'rows' => $rows,
            'filters' => [
                'entity_type' => $entityType,
                'q' => $search,
            ],
            'entityTypes' => self::ENTITY_TYPES,
        ]);
    }

    public function create(Request $request): View
    {
        $selectedEntityType = strtoupper(trim((string) $request->query('entity_type', 'CMS_PAGE')));

        return view('admin.seo.form', [
            'seo' => new SeoMeta(),
            'formMode' => 'create',
            'entityTypes' => self::ENTITY_TYPES,
            'selectedEntityType' => in_array($selectedEntityType, self::ENTITY_TYPES, true) ? $selectedEntityType : 'CMS_PAGE',
            'entityOptions' => $this->entityOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateSeo($request);

        SeoMeta::query()->create($data);

        return redirect()->route('admin.seo.index')->with('notice', 'SEO meta berhasil dibuat.');
    }

    public function edit(SeoMeta $seo): View
    {
        return view('admin.seo.form', [
            'seo' => $seo,
            'formMode' => 'edit',
            'entityTypes' => self::ENTITY_TYPES,
            'selectedEntityType' => strtoupper((string) $seo->entity_type),
            'entityOptions' => $this->entityOptions(),
        ]);
    }

    public function update(Request $request, SeoMeta $seo): RedirectResponse
    {
        $data = $this->validateSeo($request, $seo->id);
        $seo->update($data);

        return redirect()->route('admin.seo.index')->with('notice', 'SEO meta berhasil diperbarui.');
    }

    public function destroy(SeoMeta $seo): RedirectResponse
    {
        $seo->delete();

        return redirect()->route('admin.seo.index')->with('notice', 'SEO meta berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSeo(Request $request, ?int $seoId = null): array
    {
        $validated = $request->validate([
            'entity_type' => ['required', 'string', Rule::in(self::ENTITY_TYPES)],
            'entity_id' => ['required', 'integer', 'min:1'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'og_image_path' => ['nullable', 'string', 'max:255'],
        ]);

        $exists = SeoMeta::query()
            ->where('entity_type', $validated['entity_type'])
            ->where('entity_id', (int) $validated['entity_id'])
            ->when($seoId !== null, static fn ($query) => $query->where('id', '!=', $seoId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'entity_id' => 'Entity type + entity id sudah terdaftar di SEO meta.',
            ]);
        }

        if (!$this->entityExists((string) $validated['entity_type'], (int) $validated['entity_id'])) {
            throw ValidationException::withMessages([
                'entity_id' => 'Entity tidak ditemukan untuk entity type yang dipilih.',
            ]);
        }

        $validated['entity_type'] = strtoupper((string) $validated['entity_type']);
        $validated['entity_id'] = (int) $validated['entity_id'];

        return $validated;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function entityOptions(): array
    {
        return [
            'CMS_PAGE' => CmsPage::query()->select(['id', 'title', 'slug', 'type'])->orderByDesc('id')->limit(200)->get()
                ->map(static fn (CmsPage $item): array => [
                    'id' => $item->id,
                    'label' => '['.strtoupper((string) $item->type).'] '.$item->title.' ('.$item->slug.')',
                ])->all(),
            'PRODUCT' => Product::query()->select(['id', 'name', 'slug'])->orderByDesc('id')->limit(200)->get()
                ->map(static fn (Product $item): array => [
                    'id' => $item->id,
                    'label' => $item->name.' ('.$item->slug.')',
                ])->all(),
            'CATEGORY' => Category::query()->select(['id', 'name', 'slug', 'type'])->orderByDesc('id')->limit(200)->get()
                ->map(static fn (Category $item): array => [
                    'id' => $item->id,
                    'label' => '['.strtoupper((string) $item->type).'] '.$item->name.' ('.$item->slug.')',
                ])->all(),
        ];
    }

    private function entityExists(string $entityType, int $entityId): bool
    {
        return match (strtoupper($entityType)) {
            'CMS_PAGE' => CmsPage::query()->whereKey($entityId)->exists(),
            'PRODUCT' => Product::query()->whereKey($entityId)->exists(),
            'CATEGORY' => Category::query()->whereKey($entityId)->exists(),
            default => false,
        };
    }
}
