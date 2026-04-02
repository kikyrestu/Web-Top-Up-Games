<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Product;
use App\Models\ScrapedProduct;
use Illuminate\Support\Str;

class MigrateCategoriesCommand extends Command
{
    protected $signature = 'app:migrate-categories';
    protected $description = 'Split generic PPOB categories into specific sub-categories based on Scraped Data.';

    public function handle()
    {
        $products = Product::whereHas('providerMappings')->with('providerMappings')->get();
        $updatedProducts = 0;
        
        $this->info("Found {$products->count()} imported products to remap...");

        foreach ($products as $product) {
            $mapping = $product->providerMappings->first();
            if (!$mapping) continue;

            $scraped = ScrapedProduct::where('provider_product_code', $mapping->provider_product_code)->first();
            if (!$scraped) continue;

            // Re-run Smart Mapping Logic
            $brandString = strtolower($scraped->brand ?: $scraped->category_name ?: 'lainnya');
            $categoryNameLower = strtolower($scraped->category_name ?? '');
            $matchedType = 'ppob'; // Default

            // Check category_name first — most reliable indicator from scraped data
            if (in_array($categoryNameLower, ['games', 'game'])) {
                $matchedType = 'game';
            } elseif (str_contains($brandString, 'dana') || str_contains($brandString, 'ovo') || str_contains($brandString, 'gopay') || str_contains($brandString, 'shopee') || str_contains($brandString, 'linkaja') || str_contains($brandString, 'e-money') || str_contains($brandString, 'e-toll') || str_contains($brandString, 'maxim') || str_contains($brandString, 'grab') || str_contains($brandString, 'gojek') || str_contains($brandString, 'isaku') || $categoryNameLower === 'e-money') {
                $matchedType = 'e-money';
            } elseif (str_contains($brandString, 'telkomsel') || str_contains($brandString, 'indosat') || str_contains($brandString, 'xl') || str_contains($brandString, 'axis') || str_contains($brandString, 'tri') || str_contains($brandString, 'smartfren') || str_contains($brandString, 'pulsa') || str_contains($brandString, 'data') || in_array($categoryNameLower, ['pulsa', 'data', 'masa aktif', 'paket sms & telpon', 'aktivasi perdana', 'aktivasi voucher'])) {
                $matchedType = 'pulsa';
            } elseif (str_contains($brandString, 'pln') || str_contains($brandString, 'bpjs') || str_contains($brandString, 'pdam') || str_contains($brandString, 'token') || str_contains($brandString, 'tagihan') || in_array($categoryNameLower, ['pln', 'pascabayar'])) {
                $matchedType = 'tagihan';
            } elseif (str_contains($brandString, 'mobile legends') || str_contains($brandString, 'free fire') || str_contains($brandString, 'pubg') || str_contains($brandString, 'genshin') || str_contains($brandString, 'undawn') || str_contains($brandString, 'diamond') || str_contains($brandString, 'valorant') || str_contains($brandString, 'game')) {
                $matchedType = 'game';
            } elseif (str_contains($brandString, 'voucher') && !str_contains($brandString, 'tv') && !str_contains($brandString, 'vision') || $categoryNameLower === 'voucher') {
                $matchedType = 'voucher';
            } elseif (str_contains($brandString, 'tv') || str_contains($brandString, 'vision') || str_contains($brandString, 'nex') || str_contains($brandString, 'k-vision') || $categoryNameLower === 'tv') {
                $matchedType = 'tv';
            } elseif ($scraped->type === 'pasca' || $scraped->type === 'postpaid') {
                $matchedType = 'tagihan';
            } elseif ($categoryNameLower === 'gas') {
                $matchedType = 'ppob';
            }

            // 2. Logic Kategorisasi Spesifik (Menghindari Penggabungan Merek Secara Total)
            $originalBrand = $scraped->brand ?: 'Lainnya';
            $finalCategoryName = ucwords(strtolower($originalBrand)); // Default: sekadar nama Merek
            
            // Pemisahan Pulsa dan Data
            if (in_array($matchedType, ['pulsa', 'data', 'tagihan', 'voucher'])) {
                if (!empty($scraped->category_name) && strtolower($scraped->category_name) !== strtolower($scraped->brand)) {
                    $finalCategoryName = ucwords(strtolower($scraped->category_name . ' ' . $scraped->brand));
                }
            }
            
            // Pemisahan E-Money dengan paket spesifik
            $prodNameLower = strtolower($scraped->product_name);
            if (str_contains($prodNameLower, 'maxim customer')) {
                $finalCategoryName = 'Maxim Customer';
            } elseif (str_contains($prodNameLower, 'maxim driver')) {
                $finalCategoryName = 'Maxim Driver';
            } elseif (str_contains($prodNameLower, 'grab penumpang')) {
                $finalCategoryName = 'Grab Penumpang';
            } elseif (str_contains($prodNameLower, 'grab driver')) {
                $finalCategoryName = 'Grab Driver';
            }

            // Hindari duplikasi
            if (strtolower($finalCategoryName) === 'pulsa pulsa') $finalCategoryName = 'Pulsa';

            // Find or create category based on brand
            $category = Category::firstOrCreate(
                ['name' => $finalCategoryName],
                [
                    'slug'       => Str::slug($finalCategoryName),
                    'is_active'  => true,
                    'sort_order' => 99,
                    'type'       => $matchedType,
                ]
            );

            if ($product->category_id !== $category->id) {
                $oldCategory = $product->category->name ?? 'Unknown';
                $product->category_id = $category->id;
                $product->save();
                $updatedProducts++;
                $this->info("Moved [{$product->name}] from '{$oldCategory}' to '{$category->name}'");
            }
        }

        $this->info("Total products moved: $updatedProducts");
        
        // Cleanup empty categories
        $emptyCategories = Category::whereDoesntHave('products')->get();
        if ($emptyCategories->count() > 0) {
            foreach ($emptyCategories as $emptyCat) {
                $this->info("Deleting empty category: {$emptyCat->name}");
                $emptyCat->delete();
            }
        }

        return self::SUCCESS;
    }
}
