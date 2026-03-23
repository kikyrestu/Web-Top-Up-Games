<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AdminCatalogController extends Controller
{
    public function categoriesIndex(Request $request): View
    {
        $type = strtoupper(trim((string) $request->query('type', '')));
        $search = trim((string) $request->query('q', ''));

        $categories = Category::query()
            ->withCount('products')
            ->when($type !== '', static fn ($query) => $query->whereRaw('UPPER(type) = ?', [$type]))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.catalog.categories-index', [
            'rows' => $categories,
            'filters' => [
                'type' => $type,
                'q' => $search,
            ],
        ]);
    }

    public function categoriesCreate(): View
    {
        return view('admin.catalog.categories-form', [
            'row' => new Category(),
            'formMode' => 'create',
        ]);
    }

    public function categoriesStore(Request $request): RedirectResponse
    {
        $data = $this->validateCategory($request);
        Category::query()->create($data);

        return redirect()->route('admin.catalog.categories.index')->with('notice', 'Kategori berhasil dibuat.');
    }

    public function categoriesEdit(Category $category): View
    {
        return view('admin.catalog.categories-form', [
            'row' => $category,
            'formMode' => 'edit',
        ]);
    }

    public function categoriesUpdate(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validateCategory($request, $category->id);
        $category->update($data);

        return redirect()->route('admin.catalog.categories.index')->with('notice', 'Kategori berhasil diperbarui.');
    }

    public function categoriesDestroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors([
                'category' => 'Kategori tidak bisa dihapus karena masih memiliki produk.',
            ]);
        }

        $category->delete();

        return redirect()->route('admin.catalog.categories.index')->with('notice', 'Kategori berhasil dihapus.');
    }

    public function productsIndex(Request $request): View
    {
        $type = strtoupper(trim((string) $request->query('type', '')));
        $categoryId = (int) $request->integer('category_id');
        $search = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->with('category:id,name')
            ->withCount(['providerPrices', 'providerProducts'])
            ->when($type !== '', static fn ($query) => $query->whereRaw('UPPER(type) = ?', [$type]))
            ->when($categoryId > 0, static fn ($query) => $query->where('category_id', $categoryId))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.catalog.products-index', [
            'rows' => $products,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'type' => $type,
                'category_id' => $categoryId,
                'q' => $search,
            ],
        ]);
    }

    public function productsCreate(): View
    {
        return view('admin.catalog.products-form', [
            'row' => new Product(),
            'formMode' => 'create',
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function productsStore(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        Product::query()->create($data);

        return redirect()->route('admin.catalog.products.index')->with('notice', 'Produk berhasil dibuat.');
    }

    public function productsEdit(Product $product): View
    {
        return view('admin.catalog.products-form', [
            'row' => $product,
            'formMode' => 'edit',
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function productsUpdate(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validateProduct($request, $product->id);
        $product->update($data);

        return redirect()->route('admin.catalog.products.index')->with('notice', 'Produk berhasil diperbarui.');
    }

    public function productsDestroy(Product $product): RedirectResponse
    {
        if ($product->providerPrices()->exists() || $product->providerProducts()->exists()) {
            return back()->withErrors([
                'product' => 'Produk tidak bisa dihapus karena masih terhubung ke provider prices/provider products.',
            ]);
        }

        $product->delete();

        return redirect()->route('admin.catalog.products.index')->with('notice', 'Produk berhasil dihapus.');
    }

    public function providersIndex(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $providers = Provider::query()
            ->withCount(['prices', 'providerProducts'])
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('code', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.catalog.providers-index', [
            'rows' => $providers,
            'filters' => [
                'q' => $search,
            ],
        ]);
    }

    public function providersCreate(): View
    {
        return view('admin.catalog.providers-form', [
            'row' => new Provider(),
            'formMode' => 'create',
        ]);
    }

    public function providersStore(Request $request): RedirectResponse
    {
        $data = $this->validateProvider($request);
        Provider::query()->create($data);

        return redirect()->route('admin.catalog.providers.index')->with('notice', 'Provider berhasil dibuat.');
    }

    public function providersEdit(Provider $provider): View
    {
        return view('admin.catalog.providers-form', [
            'row' => $provider,
            'formMode' => 'edit',
        ]);
    }

    public function providersUpdate(Request $request, Provider $provider): RedirectResponse
    {
        $data = $this->validateProvider($request, $provider->id);
        $provider->update($data);

        return redirect()->route('admin.catalog.providers.index')->with('notice', 'Provider berhasil diperbarui.');
    }

    public function providersDestroy(Provider $provider): RedirectResponse
    {
        if ($provider->prices()->exists() || $provider->providerProducts()->exists()) {
            return back()->withErrors([
                'provider' => 'Provider tidak bisa dihapus karena masih terhubung ke data produk/harga provider.',
            ]);
        }

        $provider->delete();

        return redirect()->route('admin.catalog.providers.index')->with('notice', 'Provider berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request, ?int $categoryId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('categories', 'slug')->ignore($categoryId)],
            'type' => ['required', 'string', Rule::in(['TOPUP', 'PPOB'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slugRaw = trim((string) ($validated['slug'] ?? ''));
        $slug = $slugRaw !== '' ? Str::slug($slugRaw) : Str::slug((string) $validated['name']);

        $validated['slug'] = $slug !== '' ? $slug : 'category-'.Str::lower(Str::random(8));
        $validated['type'] = strtoupper((string) $validated['type']);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request, ?int $productId = null): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'type' => ['required', 'string', Rule::in(['TOPUP', 'PPOB'])],
            'meta_json' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slugRaw = trim((string) ($validated['slug'] ?? ''));
        $slug = $slugRaw !== '' ? Str::slug($slugRaw) : Str::slug((string) $validated['name']);
        $validated['slug'] = $slug !== '' ? $slug : 'product-'.Str::lower(Str::random(8));
        $validated['type'] = strtoupper((string) $validated['type']);
        $validated['is_active'] = $request->boolean('is_active');

        $metaRaw = trim((string) ($validated['meta_json'] ?? ''));
        $validated['meta'] = $this->decodeJsonField($metaRaw, 'meta_json');

        unset($validated['meta_json']);

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProvider(Request $request, ?int $providerId = null): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('providers', 'code')->ignore($providerId)],
            'name' => ['required', 'string', 'max:100'],
            'settings_json' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper(trim((string) $validated['code']));
        $validated['name'] = trim((string) $validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        $settingsRaw = trim((string) ($validated['settings_json'] ?? ''));
        $validated['settings'] = $this->decodeJsonField($settingsRaw, 'settings_json');

        unset($validated['settings_json']);

        return $validated;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJsonField(string $raw, string $fieldName): ?array
    {
        if ($raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages([
                $fieldName => 'Format JSON tidak valid.',
            ]);
        }

        return $decoded;
    }
}
