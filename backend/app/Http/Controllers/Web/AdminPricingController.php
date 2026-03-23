<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Margin;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class AdminPricingController extends Controller
{
    public function marginsIndex(Request $request): View
    {
        $scope = strtoupper(trim((string) $request->query('scope', '')));
        $mode = strtoupper(trim((string) $request->query('mode', '')));

        $rows = Margin::query()
            ->with(['product:id,name,sku', 'category:id,name'])
            ->when(in_array($scope, ['PRODUCT', 'CATEGORY', 'GLOBAL'], true), static function ($query) use ($scope): void {
                if ($scope === 'PRODUCT') {
                    $query->whereNotNull('product_id');
                    return;
                }

                if ($scope === 'CATEGORY') {
                    $query->whereNull('product_id')->whereNotNull('category_id');
                    return;
                }

                $query->whereNull('product_id')->whereNull('category_id');
            })
            ->when(in_array($mode, ['FLAT', 'PERCENTAGE'], true), static fn ($query) => $query->where('mode', $mode))
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pricing.margins-index', [
            'rows' => $rows,
            'filters' => [
                'scope' => $scope,
                'mode' => $mode,
            ],
        ]);
    }

    public function marginsCreate(): View
    {
        return view('admin.pricing.margins-form', [
            'row' => new Margin(),
            'formMode' => 'create',
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'category_id']),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function marginsStore(Request $request): RedirectResponse
    {
        $data = $this->validateMargin($request);
        Margin::query()->create($data);

        return redirect()->route('admin.pricing.margins.index')->with('notice', 'Margin rule berhasil dibuat.');
    }

    public function marginsEdit(Margin $margin): View
    {
        return view('admin.pricing.margins-form', [
            'row' => $margin,
            'formMode' => 'edit',
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'category_id']),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function marginsUpdate(Request $request, Margin $margin): RedirectResponse
    {
        $data = $this->validateMargin($request);
        $margin->update($data);

        return redirect()->route('admin.pricing.margins.index')->with('notice', 'Margin rule berhasil diperbarui.');
    }

    public function marginsDestroy(Margin $margin): RedirectResponse
    {
        $margin->delete();

        return redirect()->route('admin.pricing.margins.index')->with('notice', 'Margin rule berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMargin(Request $request): array
    {
        $validated = $request->validate([
            'scope' => ['required', 'in:PRODUCT,CATEGORY,GLOBAL'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'mode' => ['required', 'in:FLAT,PERCENTAGE'],
            'value' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $scope = strtoupper((string) $validated['scope']);
        $productId = isset($validated['product_id']) ? (int) $validated['product_id'] : null;
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;

        if ($scope === 'PRODUCT') {
            if ($productId === null || $productId <= 0) {
                throw ValidationException::withMessages([
                    'product_id' => 'Product wajib dipilih untuk scope PRODUCT.',
                ]);
            }

            $product = Product::query()->select(['id', 'category_id'])->find($productId);
            $categoryId = $product?->category_id !== null ? (int) $product->category_id : $categoryId;
        }

        if ($scope === 'CATEGORY') {
            if ($categoryId === null || $categoryId <= 0) {
                throw ValidationException::withMessages([
                    'category_id' => 'Category wajib dipilih untuk scope CATEGORY.',
                ]);
            }

            $productId = null;
        }

        if ($scope === 'GLOBAL') {
            $productId = null;
            $categoryId = null;
        }

        return [
            'product_id' => $productId,
            'category_id' => $categoryId,
            'mode' => strtoupper((string) $validated['mode']),
            'value' => (float) $validated['value'],
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
