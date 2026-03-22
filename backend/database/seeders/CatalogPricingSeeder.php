<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Margin;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ProviderProduct;
use App\Models\ProviderPrice;
use Illuminate\Database\Seeder;

class CatalogPricingSeeder extends Seeder
{
    public function run(): void
    {
        $topupCategory = Category::query()->updateOrCreate(
            ['slug' => 'top-up-game'],
            ['name' => 'Top Up Game', 'type' => 'TOPUP', 'is_active' => true]
        );

        $multifinanceCategory = Category::query()->updateOrCreate(
            ['slug' => 'multifinance'],
            ['name' => 'Multifinance', 'type' => 'MULTIFINANCE', 'is_active' => true]
        );

        $mlProduct = Product::query()->updateOrCreate(
            ['sku' => 'ML-86-DM'],
            [
                'category_id' => $topupCategory->id,
                'name' => 'Mobile Legends 86 Diamond',
                'slug' => 'mobile-legends-86-diamond',
                'type' => 'TOPUP',
                'is_active' => true,
            ]
        );

        $finProduct = Product::query()->updateOrCreate(
            ['sku' => 'MF-TAGIHAN-PLN'],
            [
                'category_id' => $multifinanceCategory->id,
                'name' => 'Tagihan Listrik PLN',
                'slug' => 'tagihan-listrik-pln',
                'type' => 'MULTIFINANCE',
                'is_active' => true,
            ]
        );

        Margin::query()->updateOrCreate(
            ['category_id' => $topupCategory->id, 'product_id' => null],
            ['mode' => 'FLAT', 'value' => 1500, 'is_active' => true]
        );

        Margin::query()->updateOrCreate(
            ['category_id' => $multifinanceCategory->id, 'product_id' => null],
            ['mode' => 'FLAT', 'value' => 1000, 'is_active' => true]
        );

        $providerMap = Provider::query()->pluck('id', 'code');

        $topupRows = [
            ['code' => 'DIGIFLAZZ', 'base' => 19000, 'admin' => 0, 'commission' => 200],
            ['code' => 'RAJABILLER', 'base' => 19200, 'admin' => 0, 'commission' => 250],
            ['code' => 'ORDERKUOTA', 'base' => 19500, 'admin' => 0, 'commission' => 150],
        ];

        foreach ($topupRows as $row) {
            if (!isset($providerMap[$row['code']])) {
                continue;
            }

            ProviderPrice::query()->updateOrCreate(
                [
                    'provider_id' => $providerMap[$row['code']],
                    'product_id' => $mlProduct->id,
                ],
                [
                    'base_price' => $row['base'],
                    'admin_fee' => $row['admin'],
                    'commission' => $row['commission'],
                    'is_active' => true,
                    'provider_updated_at' => now(),
                ]
            );

            ProviderProduct::query()->updateOrCreate(
                [
                    'provider_id' => $providerMap[$row['code']],
                    'product_id' => $mlProduct->id,
                ],
                [
                    'provider_product_code' => 'SKU-'.$row['code'].'-ML86',
                    'provider_product_name' => 'Mobile Legends 86 Diamond',
                    'is_available' => true,
                ]
            );
        }

        $multifinanceRows = [
            ['code' => 'DIGIFLAZZ', 'base' => 50000, 'admin' => 0, 'commission' => 100],
            ['code' => 'RAJABILLER', 'base' => 49700, 'admin' => 2500, 'commission' => 500],
            ['code' => 'ORDERKUOTA', 'base' => 49900, 'admin' => 2500, 'commission' => 650],
        ];

        foreach ($multifinanceRows as $row) {
            if (!isset($providerMap[$row['code']])) {
                continue;
            }

            ProviderPrice::query()->updateOrCreate(
                [
                    'provider_id' => $providerMap[$row['code']],
                    'product_id' => $finProduct->id,
                ],
                [
                    'base_price' => $row['base'],
                    'admin_fee' => $row['admin'],
                    'commission' => $row['commission'],
                    'is_active' => true,
                    'provider_updated_at' => now(),
                ]
            );

            ProviderProduct::query()->updateOrCreate(
                [
                    'provider_id' => $providerMap[$row['code']],
                    'product_id' => $finProduct->id,
                ],
                [
                    'provider_product_code' => 'SKU-'.$row['code'].'-MFPLN',
                    'provider_product_name' => 'Tagihan Listrik PLN',
                    'is_available' => true,
                ]
            );
        }
    }
}
