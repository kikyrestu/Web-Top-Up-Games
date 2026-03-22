<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Pricing\Services\PricingEngineService;
use App\Http\Controllers\Controller;
use App\Models\Margin;
use App\Models\Product;
use App\Models\ProviderPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class QuoteController extends Controller
{
    public function __construct(private readonly PricingEngineService $pricingEngine)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:10'],
            'customer_target' => ['nullable', 'string', 'max:120'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);

        $product = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->find($validated['product_id']);

        if ($product === null) {
            return response()->json([
                'success' => false,
                'code' => 'PRODUCT_NOT_AVAILABLE',
                'message' => 'Selected product is not available',
                'data' => null,
            ], 422);
        }

        $rows = ProviderPrice::query()
            ->with('provider')
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->get()
            ->filter(static fn (ProviderPrice $price): bool => (bool) ($price->provider?->is_active))
            ->values();

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'code' => 'NO_PROVIDER_CANDIDATE',
                'message' => 'No active provider pricing found for selected product',
                'data' => null,
            ], 422);
        }

        $candidates = $rows->map(static function (ProviderPrice $row): array {
            $basePrice = (float) $row->base_price;
            $adminFee = (float) $row->admin_fee;
            $commission = (float) $row->commission;

            return [
                'provider_id' => $row->provider_id,
                'provider_code' => $row->provider?->code,
                'provider_name' => $row->provider?->name,
                'base_price' => $basePrice,
                'admin_fee' => $adminFee,
                'commission' => $commission,
                'total_cost' => $basePrice + $adminFee,
            ];
        })->all();

        $ranked = $this->pricingEngine->rankCandidates($candidates, (string) $product->type);
        $selected = $ranked[0];

        $margin = $this->resolveMargin($product->id, (int) $product->category_id, (float) $selected['base_price']);
        $unitPrice = (float) $selected['base_price'] + (float) $selected['admin_fee'] + $margin;
        $finalAmount = $unitPrice * $quantity;

        $quoteToken = (string) Str::uuid();
        $expiresInSeconds = 120;

        Cache::put('quote_token:'.$quoteToken, [
            'product_id' => $product->id,
            'product_type' => $product->type,
            'quantity' => $quantity,
            'customer_target' => $validated['customer_target'] ?? null,
            'base_price' => (float) $selected['base_price'],
            'admin_fee' => (float) $selected['admin_fee'],
            'margin' => $margin,
            'final_amount' => $finalAmount,
            'selected_provider' => $selected,
            'candidates' => $ranked,
            'expires_at' => now()->addSeconds($expiresInSeconds)->toISOString(),
        ], now()->addSeconds($expiresInSeconds));

        return response()->json([
            'success' => true,
            'code' => 'QUOTE_CREATED',
            'message' => 'Quote generated successfully',
            'data' => [
                'quote_token' => $quoteToken,
                'expires_in_seconds' => $expiresInSeconds,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'type' => $product->type,
                ],
                'pricing' => [
                    'base_price' => (float) $selected['base_price'],
                    'admin_fee' => (float) $selected['admin_fee'],
                    'margin' => $margin,
                    'quantity' => $quantity,
                    'final_amount' => $finalAmount,
                ],
                'selected_provider' => $selected,
                'ranked_providers' => $ranked,
            ],
        ]);
    }

    private function resolveMargin(int $productId, int $categoryId, float $basePrice): float
    {
        $margin = Margin::query()
            ->where('is_active', true)
            ->where('product_id', $productId)
            ->latest('id')
            ->first();

        if ($margin === null) {
            $margin = Margin::query()
                ->where('is_active', true)
                ->whereNull('product_id')
                ->where('category_id', $categoryId)
                ->latest('id')
                ->first();
        }

        if ($margin === null) {
            return 0;
        }

        $value = (float) $margin->value;

        if (strtoupper((string) $margin->mode) === 'PERCENTAGE') {
            return round(($basePrice * $value) / 100, 2);
        }

        return round($value, 2);
    }
}
