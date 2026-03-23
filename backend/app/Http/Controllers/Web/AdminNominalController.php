<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ProviderPrice;
use App\Models\ProviderProduct;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class AdminNominalController extends Controller
{
    public function mappingsIndex(Request $request): View
    {
        $providerId = (int) $request->integer('provider_id');
        $productId = (int) $request->integer('product_id');
        $search = trim((string) $request->query('q', ''));

        $rows = ProviderProduct::query()
            ->with(['provider:id,code,name', 'product:id,name,sku'])
            ->when($providerId > 0, static fn ($query) => $query->where('provider_id', $providerId))
            ->when($productId > 0, static fn ($query) => $query->where('product_id', $productId))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('provider_product_code', 'like', '%'.$search.'%')
                        ->orWhere('provider_product_name', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.nominal.mappings-index', [
            'rows' => $rows,
            'providers' => Provider::query()->orderBy('name')->get(['id', 'code', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
            'filters' => [
                'provider_id' => $providerId,
                'product_id' => $productId,
                'q' => $search,
            ],
        ]);
    }

    public function mappingsCreate(): View
    {
        return view('admin.nominal.mappings-form', [
            'row' => new ProviderProduct(),
            'formMode' => 'create',
            'providers' => Provider::query()->orderBy('name')->get(['id', 'code', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }

    public function mappingsStore(Request $request): RedirectResponse
    {
        $data = $this->validateMapping($request);
        ProviderProduct::query()->create($data);

        return redirect()->route('admin.nominal.mappings.index')->with('notice', 'Mapping nominal provider berhasil dibuat.');
    }

    public function mappingsEdit(ProviderProduct $mapping): View
    {
        return view('admin.nominal.mappings-form', [
            'row' => $mapping,
            'formMode' => 'edit',
            'providers' => Provider::query()->orderBy('name')->get(['id', 'code', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }

    public function mappingsUpdate(Request $request, ProviderProduct $mapping): RedirectResponse
    {
        $data = $this->validateMapping($request, $mapping->id);
        $mapping->update($data);

        return redirect()->route('admin.nominal.mappings.index')->with('notice', 'Mapping nominal provider berhasil diperbarui.');
    }

    public function mappingsDestroy(ProviderProduct $mapping): RedirectResponse
    {
        $hasPrice = ProviderPrice::query()
            ->where('provider_id', $mapping->provider_id)
            ->where('product_id', $mapping->product_id)
            ->exists();

        if ($hasPrice) {
            return back()->withErrors([
                'mapping' => 'Mapping tidak bisa dihapus karena masih memiliki data provider price.',
            ]);
        }

        $mapping->delete();

        return redirect()->route('admin.nominal.mappings.index')->with('notice', 'Mapping nominal provider berhasil dihapus.');
    }

    public function pricesIndex(Request $request): View
    {
        $providerId = (int) $request->integer('provider_id');
        $productId = (int) $request->integer('product_id');

        $rows = ProviderPrice::query()
            ->with(['provider:id,code,name', 'product:id,name,sku'])
            ->when($providerId > 0, static fn ($query) => $query->where('provider_id', $providerId))
            ->when($productId > 0, static fn ($query) => $query->where('product_id', $productId))
            ->orderByDesc('provider_updated_at')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.nominal.prices-index', [
            'rows' => $rows,
            'providers' => Provider::query()->orderBy('name')->get(['id', 'code', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
            'filters' => [
                'provider_id' => $providerId,
                'product_id' => $productId,
            ],
        ]);
    }

    public function pricesCreate(): View
    {
        return view('admin.nominal.prices-form', [
            'row' => new ProviderPrice(),
            'formMode' => 'create',
            'providers' => Provider::query()->orderBy('name')->get(['id', 'code', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }

    public function pricesStore(Request $request): RedirectResponse
    {
        $data = $this->validatePrice($request);
        ProviderPrice::query()->create($data);

        return redirect()->route('admin.nominal.prices.index')->with('notice', 'Provider price berhasil dibuat.');
    }

    public function pricesEdit(ProviderPrice $price): View
    {
        return view('admin.nominal.prices-form', [
            'row' => $price,
            'formMode' => 'edit',
            'providers' => Provider::query()->orderBy('name')->get(['id', 'code', 'name']),
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }

    public function pricesUpdate(Request $request, ProviderPrice $price): RedirectResponse
    {
        $data = $this->validatePrice($request, $price->id);
        $price->update($data);

        return redirect()->route('admin.nominal.prices.index')->with('notice', 'Provider price berhasil diperbarui.');
    }

    public function pricesDestroy(ProviderPrice $price): RedirectResponse
    {
        $price->delete();

        return redirect()->route('admin.nominal.prices.index')->with('notice', 'Provider price berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMapping(Request $request, ?int $mappingId = null): array
    {
        $providerId = (int) $request->input('provider_id');

        $validated = $request->validate([
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
                Rule::unique('provider_products', 'product_id')
                    ->where(static fn ($query) => $query->where('provider_id', $providerId))
                    ->ignore($mappingId),
            ],
            'provider_product_code' => [
                'required',
                'string',
                'max:120',
                Rule::unique('provider_products', 'provider_product_code')
                    ->where(static fn ($query) => $query->where('provider_id', $providerId))
                    ->ignore($mappingId),
            ],
            'provider_product_name' => ['required', 'string', 'max:255'],
            'raw_payload_json' => ['nullable', 'string'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $validated['provider_product_code'] = trim((string) $validated['provider_product_code']);
        $validated['provider_product_name'] = trim((string) $validated['provider_product_name']);
        $validated['is_available'] = $request->boolean('is_available');

        $rawPayload = trim((string) ($validated['raw_payload_json'] ?? ''));
        $validated['raw_payload'] = $this->decodeJsonField($rawPayload, 'raw_payload_json');

        unset($validated['raw_payload_json']);

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePrice(Request $request, ?int $priceId = null): array
    {
        $providerId = (int) $request->input('provider_id');

        $validated = $request->validate([
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
                Rule::unique('provider_prices', 'product_id')
                    ->where(static fn ($query) => $query->where('provider_id', $providerId))
                    ->ignore($priceId),
            ],
            'base_price' => ['required', 'numeric', 'min:0'],
            'admin_fee' => ['nullable', 'numeric', 'min:0'],
            'commission' => ['nullable', 'numeric', 'min:0'],
            'provider_updated_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $hasMapping = ProviderProduct::query()
            ->where('provider_id', (int) $validated['provider_id'])
            ->where('product_id', (int) $validated['product_id'])
            ->exists();

        if (!$hasMapping) {
            throw ValidationException::withMessages([
                'product_id' => 'Belum ada mapping provider product untuk kombinasi provider dan product ini.',
            ]);
        }

        $validated['admin_fee'] = (float) ($validated['admin_fee'] ?? 0);
        $validated['commission'] = (float) ($validated['commission'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

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
