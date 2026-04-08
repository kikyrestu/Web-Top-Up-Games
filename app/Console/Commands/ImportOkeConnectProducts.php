<?php

namespace App\Console\Commands;

use App\Models\ApiProvider;
use App\Models\Product;
use App\Models\ProductProviderMapping;
use App\Models\ScrapedProduct;
use Illuminate\Console\Command;

class ImportOkeConnectProducts extends Command
{
    protected $signature   = 'okeconnect:import
                              {file? : Path to JSON file (default: storage/okeconnect_products.json)}
                              {--dry-run : Preview without saving}';

    protected $description = 'Import OkeConnect products from JSON file into scraped_products table.';

    public function handle(): int
    {
        $file = $this->argument('file') ?? storage_path('okeconnect_products.json');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $products = json_decode(file_get_contents($file), true);
        if (!is_array($products)) {
            $this->error('Invalid JSON file.');
            return self::FAILURE;
        }

        // Find OrderKuota provider
        $provider = ApiProvider::where('code', 'orderkuota')->first();
        if (!$provider) {
            $this->error("Provider 'orderkuota' not found in api_providers table.");
            return self::FAILURE;
        }

        $this->info("Provider: {$provider->name} (ID: {$provider->id})");
        $this->info("Products in file: " . count($products));

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN — no changes will be saved.');
            $this->showSummary($products);
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($products as $item) {
            $code = $item['provider_product_code'] ?? null;
            if (!$code) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $price = (float) ($item['price'] ?? 0);
            $type  = $item['type'] ?? 'prepaid';

            $sellSuggestion = Product::calculateSuggestedPrice($price, $type);

            // Check if already mapped to a product
            $existingMapping = ProductProviderMapping::where('api_provider_id', $provider->id)
                ->where('provider_product_code', $code)
                ->first();

            $scraped = ScrapedProduct::updateOrCreate(
                [
                    'api_provider_id'      => $provider->id,
                    'provider_product_code' => $code,
                ],
                [
                    'product_name'          => $item['product_name'],
                    'brand'                 => $item['brand'] ?? null,
                    'category_name'         => $item['category_name'] ?? null,
                    'type'                  => $type,
                    'price'                 => $price,
                    'price_sell_suggestion' => round($sellSuggestion, 2),
                    'status_provider'       => $item['status_provider'] ?? 'available',
                    'is_imported'           => $existingMapping ? true : false,
                    'imported_product_id'   => $existingMapping?->product_id,
                    'synced_at'             => now(),
                ]
            );

            if ($scraped->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }

            // Update mapping capital price if exists
            if ($existingMapping && $existingMapping->product_id) {
                $existingMapping->update(['price_capital' => $price]);

                $product = Product::with('category')->find($existingMapping->product_id);
                if ($product) {
                    $product->update([
                        'price_capital'   => $price,
                        'price_sell'      => Product::calculateSuggestedPrice($price, $product->category->type ?? 'prepaid'),
                        'status_provider' => $item['status_provider'] ?? 'available',
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Import selesai:");
        $this->info("   Created: {$created}");
        $this->info("   Updated: {$updated}");
        $this->info("   Skipped: {$skipped}");

        $total = ScrapedProduct::where('api_provider_id', $provider->id)->count();
        $this->info("   Total OkeConnect products in DB: {$total}");

        return self::SUCCESS;
    }

    private function showSummary(array $products): void
    {
        $categories = [];
        $types      = ['prepaid' => 0, 'pasca' => 0];

        foreach ($products as $p) {
            $cat = $p['category_name'] ?? 'Unknown';
            $categories[$cat] = ($categories[$cat] ?? 0) + 1;

            $t = $p['type'] ?? 'prepaid';
            $types[$t] = ($types[$t] ?? 0) + 1;
        }

        arsort($categories);

        $this->table(['Category', 'Count'], collect($categories)->map(fn($v, $k) => [$k, $v])->values()->toArray());
        $this->table(['Type', 'Count'], collect($types)->map(fn($v, $k) => [$k, $v])->values()->toArray());
    }
}
