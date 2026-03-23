<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\PromoCampaign;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AdminPromoCampaignController extends Controller
{
    public function index(Request $request): View
    {
        $status = strtoupper(trim((string) $request->query('status', '')));
        $type = strtoupper(trim((string) $request->query('type', '')));
        $scope = strtoupper(trim((string) $request->query('scope', '')));
        $search = trim((string) $request->query('q', ''));

        $rows = PromoCampaign::query()
            ->with(['product:id,name,sku', 'category:id,name'])
            ->when($status === 'ACTIVE', static fn ($query) => $query->where('is_active', true))
            ->when($status === 'INACTIVE', static fn ($query) => $query->where('is_active', false))
            ->when(in_array($type, ['VOUCHER', 'CASHBACK'], true), static fn ($query) => $query->where('campaign_type', $type))
            ->when(in_array($scope, ['GLOBAL', 'CATEGORY', 'PRODUCT'], true), static fn ($query) => $query->where('scope', $scope))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.promo.campaigns-index', [
            'rows' => $rows,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'scope' => $scope,
                'q' => $search,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.promo.campaigns-form', [
            'row' => new PromoCampaign(),
            'formMode' => 'create',
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'category_id']),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        PromoCampaign::query()->create($data);

        return redirect()->route('admin.promo.campaigns.index')->with('notice', 'Promo campaign berhasil dibuat.');
    }

    public function edit(PromoCampaign $campaign): View
    {
        return view('admin.promo.campaigns-form', [
            'row' => $campaign,
            'formMode' => 'edit',
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'category_id']),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, PromoCampaign $campaign): RedirectResponse
    {
        $data = $this->validateData($request, $campaign->id);
        $campaign->update($data);

        return redirect()->route('admin.promo.campaigns.index')->with('notice', 'Promo campaign berhasil diperbarui.');
    }

    public function destroy(PromoCampaign $campaign): RedirectResponse
    {
        $campaign->delete();

        return redirect()->route('admin.promo.campaigns.index')->with('notice', 'Promo campaign berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?int $campaignId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'code' => ['required', 'string', 'max:60', Rule::unique('promo_campaigns', 'code')->ignore($campaignId)],
            'campaign_type' => ['required', 'in:VOUCHER,CASHBACK'],
            'discount_mode' => ['required', 'in:FLAT,PERCENTAGE'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'quota_total' => ['nullable', 'integer', 'min:1'],
            'quota_per_user' => ['nullable', 'integer', 'min:1'],
            'scope' => ['required', 'in:GLOBAL,CATEGORY,PRODUCT'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $scope = strtoupper((string) $validated['scope']);
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;
        $productId = isset($validated['product_id']) ? (int) $validated['product_id'] : null;

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
            $categoryId = null;
            $productId = null;
        }

        return [
            'name' => trim((string) $validated['name']),
            'code' => strtoupper(trim((string) $validated['code'])),
            'campaign_type' => strtoupper((string) $validated['campaign_type']),
            'discount_mode' => strtoupper((string) $validated['discount_mode']),
            'discount_value' => (float) $validated['discount_value'],
            'min_order_amount' => (float) ($validated['min_order_amount'] ?? 0),
            'max_discount_amount' => isset($validated['max_discount_amount']) ? (float) $validated['max_discount_amount'] : null,
            'quota_total' => isset($validated['quota_total']) ? (int) $validated['quota_total'] : null,
            'quota_per_user' => isset($validated['quota_per_user']) ? (int) $validated['quota_per_user'] : null,
            'scope' => $scope,
            'category_id' => $categoryId,
            'product_id' => $productId,
            'start_at' => $validated['start_at'] ?? null,
            'end_at' => $validated['end_at'] ?? null,
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
