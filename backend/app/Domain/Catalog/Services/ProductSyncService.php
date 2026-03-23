<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

use App\Domain\Provider\Contracts\ProviderAdapterInterface;
use App\Domain\Provider\Services\DigiflazzAdapter;
use App\Domain\Provider\Services\OrderkuotaAdapter;
use App\Domain\Provider\Services\RajabillerAdapter;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ProviderPrice;
use App\Models\ProviderProduct;
use Illuminate\Support\Str;

final class ProductSyncService
{
    public function __construct(
        private readonly DigiflazzAdapter $digiflazzAdapter,
        private readonly RajabillerAdapter $rajabillerAdapter,
        private readonly OrderkuotaAdapter $orderkuotaAdapter,
    ) {
    }

    /**
     * Sync catalog from all active providers.
     *
     * @return int Count of updated records.
     */
    public function syncAll(): int
    {
        $updated = 0;

        $providers = Provider::query()
            ->where('is_active', true)
            ->get();

        foreach ($providers as $provider) {
            $adapter = $this->resolveAdapter((string) $provider->code);

            if ($adapter === null) {
                continue;
            }

            $rows = $adapter->syncProducts();

            foreach ($rows as $row) {
                $providerProductCode = (string) ($row['provider_product_code'] ?? '');
                $providerProductName = (string) ($row['provider_product_name'] ?? '');

                if ($providerProductCode === '' || $providerProductName === '') {
                    continue;
                }

                $existingMapping = ProviderProduct::query()
                    ->where('provider_id', $provider->id)
                    ->where('provider_product_code', $providerProductCode)
                    ->first();

                $product = $existingMapping?->product;

                if ($product === null) {
                    $product = Product::query()
                        ->where('sku', $providerProductCode)
                        ->orWhere('slug', Str::slug($providerProductName))
                        ->first();
                }

                if ($product === null) {
                    continue;
                }

                ProviderProduct::query()->updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'provider_product_code' => $providerProductCode,
                        'provider_product_name' => $providerProductName,
                        'is_available' => true,
                        'raw_payload' => $row['raw_payload'] ?? null,
                    ]
                );

                ProviderPrice::query()->updateOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'base_price' => (float) ($row['base_price'] ?? 0),
                        'admin_fee' => (float) ($row['admin_fee'] ?? 0),
                        'commission' => (float) ($row['commission'] ?? 0),
                        'is_active' => true,
                        'provider_updated_at' => now(),
                    ]
                );

                $updated++;
            }
        }

        return $updated;
    }

    private function resolveAdapter(string $providerCode): ?ProviderAdapterInterface
    {
        return match (strtoupper($providerCode)) {
            'DIGIFLAZZ' => $this->digiflazzAdapter,
            'RAJABILLER' => $this->rajabillerAdapter,
            'ORDERKUOTA' => $this->orderkuotaAdapter,
            default => null,
        };
    }
}
