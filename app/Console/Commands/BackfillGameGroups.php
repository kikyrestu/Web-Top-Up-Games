<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Admin\ScrapedProductController;
use Illuminate\Console\Command;

class BackfillGameGroups extends Command
{
    protected $signature   = 'products:fix-groups {--dry-run : Tampilkan perubahan tanpa menyimpan} {--category= : Hanya proses kategori tertentu (nama)}';
    protected $description = 'Re-detect product_type & product_group untuk semua produk game berdasarkan region/server detection terbaru.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $catFilter = $this->option('category');

        $query = Category::whereIn('type', Category::GAME_TYPES)->orderBy('name');
        if ($catFilter) {
            $query->where('name', 'like', "%{$catFilter}%");
        }
        $categories = $query->get();

        $this->info("Game categories: {$categories->count()}");
        $this->newLine();

        $controller = app(ScrapedProductController::class);
        $detectGroup = new \ReflectionMethod($controller, 'detectProductGroup');
        $detectGroup->setAccessible(true);
        $detectType = new \ReflectionMethod($controller, 'detectProductType');
        $detectType->setAccessible(true);

        $updated = 0;
        $unchanged = 0;
        $rows = [];

        foreach ($categories as $cat) {
            $products = Product::where('category_id', $cat->id)->get();

            foreach ($products as $product) {
                $brand = $cat->name;
                $productName = $product->name;

                $newGroup = $detectGroup->invoke($controller, $brand, $productName, $cat->name);
                $newType  = $detectType->invoke($controller, $brand, $productName, $cat->name, $newGroup);

                $oldGroup = $product->product_group;
                $oldType  = $product->product_type;

                if ($oldGroup === $newGroup && $oldType === $newType) {
                    $unchanged++;
                    continue;
                }

                $rows[] = [
                    $cat->name,
                    mb_substr($product->name, 0, 45),
                    $oldType ?: '-',
                    $newType ?: '-',
                    $oldGroup ?: '-',
                    $newGroup ?: '-',
                ];

                if (!$dryRun) {
                    $product->update([
                        'product_group' => $newGroup,
                        'product_type'  => $newType,
                    ]);
                }
                $updated++;
            }
        }

        if (!empty($rows)) {
            $this->table(['Category', 'Product', 'Old Type', 'New Type', 'Old Group', 'New Group'], $rows);
        }

        $this->newLine();
        $label = $dryRun ? 'Would update' : 'Updated';
        $this->info("{$label}: {$updated} | Unchanged: {$unchanged}");

        if ($dryRun && $updated > 0) {
            $this->warn('Jalankan tanpa --dry-run untuk menyimpan perubahan.');
        }

        return self::SUCCESS;
    }
}
