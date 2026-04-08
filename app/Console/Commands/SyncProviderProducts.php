<?php

namespace App\Console\Commands;

use App\Models\ApiProvider;
use App\Models\ProductProviderMapping;
use App\Models\ScrapedProduct;
use App\Services\Provider\ProviderSyncFactory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncProviderProducts extends Command
{
    protected $signature   = 'providers:sync {--provider= : Kode provider spesifik. Kosong = sync semua}';
    protected $description = 'Sinkronisasi daftar produk dari semua API provider aktif.';

    public function handle(): int
    {
        $providerCode = $this->option('provider');

        $query = ApiProvider::where('is_active', true);
        if ($providerCode) {
            $query->where('code', $providerCode);
        }

        $providers = $query->get();

        if ($providers->isEmpty()) {
            $this->warn('Tidak ada provider aktif yang ditemukan.');
            return self::SUCCESS;
        }

        $totalSynced = 0;
        $errors      = [];

        foreach ($providers as $provider) {
            if (!ProviderSyncFactory::supportsSync($provider->code)) {
                $this->line("  ⏭  [{$provider->name}] Belum support sync, dilewati.");
                continue;
            }

            $this->info("  🔄 Syncing [{$provider->name}]...");

            try {
                $service     = ProviderSyncFactory::resolve($provider);
                $credentials = $provider->credentials ?? [];

                // Fetch prepaid products
                $products = $service->getPriceList($credentials);

                // For Digiflazz, also fetch pasca (postpaid) products
                if (strtolower($provider->code) === 'digiflazz') {
                    $pascaProducts = $service->getPriceList($credentials, ['cmd' => 'pasca']);
                    $this->info("      📋 Prepaid: " . count($products) . ", Pasca: " . count($pascaProducts));
                    $products = array_merge($products, $pascaProducts);
                }

                $count = 0;

                foreach ($products as $item) {
                    $sellSuggestion = \App\Models\Product::calculateSuggestedPrice($item['price'], $item['type'] ?? 'prepaid');

                    $existingMapping = ProductProviderMapping::where('api_provider_id', $provider->id)
                        ->where('provider_product_code', $item['provider_product_code'])
                        ->first();

                    ScrapedProduct::updateOrCreate(
                        [
                            'api_provider_id'      => $provider->id,
                            'provider_product_code' => $item['provider_product_code'],
                        ],
                        [
                            'product_name'          => $item['product_name'],
                            'brand'                 => $item['brand'] ?? null,
                            'category_name'         => $item['category_name'] ?? null,
                            'type'                  => $item['type'] ?? 'prepaid',
                            'price'                 => $item['price'],
                            'price_sell_suggestion' => round($sellSuggestion, 2),
                            'status_provider'       => $item['status_provider'] ?? 'available',
                            'is_imported'           => $existingMapping ? true : false,
                            'imported_product_id'   => $existingMapping?->product_id,
                            'synced_at'             => now(),
                        ]
                    );

                    if ($existingMapping && $existingMapping->product_id) {
                        // Update mapping capital price
                        $existingMapping->update([
                            'price_capital' => $item['price'],
                        ]);

                        // Update actual product price and provider status
                        $product = \App\Models\Product::with('category')->find($existingMapping->product_id);
                        if ($product) {
                            $product->update([
                                'price_capital'    => $item['price'],
                                'price_sell'       => \App\Models\Product::calculateSuggestedPrice($item['price'], $product->category->type ?? 'prepaid'),
                                'status_provider'  => $item['status_provider'] ?? 'available',
                            ]);
                        }
                    }

                    $count++;
                    $totalSynced++;
                }

                $this->info("      ✅ {$count} produk berhasil disync dari {$provider->name}.");
                Log::info("SyncProviderProducts: [{$provider->name}] {$count} products synced.");

            } catch (\Exception $e) {
                $msg = "{$provider->name}: " . $e->getMessage();
                $errors[] = $msg;
                $this->error("      ❌ Error: {$msg}");
                Log::error("SyncProviderProducts error [{$provider->name}]: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Selesai! Total produk tersync: {$totalSynced}");

        if (!empty($errors)) {
            $this->warn('Errors:');
            foreach ($errors as $err) {
                $this->line("  - {$err}");
            }
        }

        return self::SUCCESS;
    }
}
