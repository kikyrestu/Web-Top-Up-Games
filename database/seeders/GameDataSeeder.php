<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class GameDataSeeder extends Seeder
{
    public function run(): void
    {
        $ml = Category::firstOrCreate(['name' => 'Mobile Legends'], [
            'description' => 'Topup Diamond MLBB',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $ff = Category::firstOrCreate(['name' => 'Free Fire'], [
            'description' => 'Topup Diamond FF',
            'is_active' => true,
            'sort_order' => 2
        ]);

        Product::firstOrCreate(['provider_product_code' => 'ML86'], [
            'category_id' => $ml->id,
            'name' => '86 Diamonds',
            'description' => '86 Diamonds Mobile Legends',
            'price_capital' => 20000,
            'price_sell' => 22500,
            'is_active' => true
        ]);
        
        Product::firstOrCreate(['provider_product_code' => 'FF140'], [
            'category_id' => $ff->id,
            'name' => '140 Diamonds',
            'description' => '140 Diamonds Free Fire',
            'price_capital' => 20000,
            'price_sell' => 23000,
            'is_active' => true
        ]);
    }
}
