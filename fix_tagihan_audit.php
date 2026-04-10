<?php
/**
 * Re-audit tagihan products: reactivate wrongly deactivated products,
 * add Rajabiller as fallback to OkeConnect Multi Finance products.
 *
 * Run: php artisan tinker < fix_tagihan_audit.php
 */

use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    // === 1. REACTIVATE CATEGORIES ===
    $catCount = \App\Models\Category::whereIn('id', [296, 300])->update(['is_active' => true]);
    echo "Reactivated $catCount categories (296=KARTU KREDIT, 300=SAMSAT)\n";

    // === 2. REACTIVATE UNIQUE PRODUCTS ===
    $reactivateIds = [
        // Kartu Kredit (9) - all Rajabiller-only, no OkeConnect equiv
        2532, 2533, 2534, 2535, 2536, 2537, 2538, 2539, 2540,
        // Samsat (3) - all Rajabiller-only
        2838, 2839, 2840,
        // TV Berlangganan (3) - Rajabiller-only
        1578, 3132, 3150,
        // Asuransi (2) - Rajabiller-only
        1624, 1626,
        // Multi Finance unique (6) - no OkeConnect equivalent
        2597, // HCI
        2600, // MEGA FINANCE
        2601, // Mekaar ULAMM
        2603, // Mandiri Tunas Finance
        2604, // Mandiri Utama Finance - Mobil
        2605, // Mandiri Utama Finance - Motor
    ];
    $updated = \App\Models\Product::whereIn('id', $reactivateIds)->update(['is_active' => true]);
    echo "Reactivated $updated products\n";

    // === 3. ADD RAJABILLER FALLBACK MAPPINGS TO OKECONNECT PRODUCTS ===
    // For Multi Finance products that have OkeConnect equivalent,
    // add Rajabiller as fallback provider mapping
    $fallbacks = [
        3977 => 'FNFIF',       // Bayar Tagihan FIF
        3990 => 'FNADIRAH',    // Bayar Tagihan Adira Finance
        3976 => 'FNBIMA',      // Bayar Tagihan Bima Finance
        3958 => 'FNMAF',       // Bayar Tagihan Mega Auto Finance
        3989 => 'FNMEGA',      // Bayar Tagihan Mega Central Finance
        3973 => 'FNMPM',       // Bayar Tagihan MPM Finance
        3978 => 'FNOTOKD',     // Bayar Tagihan OTO Finance
        3965 => 'FNRADA',      // Bayar Tagihan Radana Finance
        3968 => 'FNSFI',       // Bayar Tagihan Suzuki Finance
        3972 => 'FNSMF',       // Bayar Tagihan Smart Finance
        3970 => 'FNBAF',       // Bayar Tagihan BAF
    ];

    $added = 0;
    foreach ($fallbacks as $productId => $rjCode) {
        $exists = \App\Models\ProductProviderMapping::where('product_id', $productId)
            ->where('api_provider_id', 4)->exists();
        if (!$exists) {
            \App\Models\ProductProviderMapping::create([
                'product_id' => $productId,
                'api_provider_id' => 4,
                'provider_product_code' => $rjCode,
                'is_active' => true,
            ]);
            $added++;
        }
    }
    echo "Added $added Rajabiller fallback mappings to OkeConnect products\n";

    DB::commit();
    echo "=== ALL DONE - Transaction committed! ===\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
