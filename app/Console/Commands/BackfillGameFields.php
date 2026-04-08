<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class BackfillGameFields extends Command
{
    protected $signature   = 'categories:fix-fields {--dry-run : Tampilkan perubahan tanpa menyimpan}';
    protected $description = 'Re-detect dan perbaiki input_fields semua kategori game berdasarkan registry terbaru.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $categories = Category::whereIn('type', Category::GAME_TYPES)->orderBy('name')->get();

        $this->info("Total game categories: {$categories->count()}");
        $this->newLine();

        $updated = 0;
        $unchanged = 0;
        $rows = [];

        foreach ($categories as $cat) {
            $current  = $cat->input_fields ?? [];
            $detected = Category::detectInputFields($cat->type, $cat->name);

            // Normalize for comparison
            $currentKey  = $this->fieldsKey($current);
            $detectedKey = $this->fieldsKey($detected);

            if ($currentKey === $detectedKey) {
                $unchanged++;
                continue;
            }

            $oldLabel = $this->describeFields($current);
            $newLabel = $this->describeFields($detected);

            $rows[] = [$cat->name, $cat->type, $oldLabel, $newLabel];

            if (!$dryRun) {
                $cat->update(['input_fields' => $detected ?: null]);
            }
            $updated++;
        }

        if (!empty($rows)) {
            $this->table(['Category', 'Type', 'Old Fields', 'New Fields'], $rows);
        }

        $this->newLine();
        $label = $dryRun ? 'Would update' : 'Updated';
        $this->info("{$label}: {$updated} | Unchanged: {$unchanged}");

        if ($dryRun && $updated > 0) {
            $this->warn('Jalankan tanpa --dry-run untuk menyimpan perubahan.');
        }

        return self::SUCCESS;
    }

    private function fieldsKey(array $fields): string
    {
        return collect($fields)->map(fn($f) => ($f['name'] ?? '') . ':' . ($f['label'] ?? ''))->implode('|');
    }

    private function describeFields(array $fields): string
    {
        if (empty($fields)) {
            return '[VOUCHER]';
        }

        return collect($fields)->map(fn($f) => $f['label'] ?? $f['name'] ?? '?')->implode(' + ');
    }
}
