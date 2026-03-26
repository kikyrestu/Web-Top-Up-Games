<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiProvider;
use App\Models\ScrapedProduct;
use App\Services\Provider\ProviderSyncFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductSyncController extends Controller
{
    public function index()
    {
        $providers = ApiProvider::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($provider) {
                $provider->supports_sync = ProviderSyncFactory::supportsSync($provider->code);
                return $provider;
            });

        $lastSync = ScrapedProduct::max('synced_at');
        $syncStats = [
            'total'    => ScrapedProduct::count(),
            'imported' => ScrapedProduct::where('is_imported', true)->count(),
            'new'      => ScrapedProduct::where('is_imported', false)->count(),
        ];

        return view('admin.product_sync.index', compact('providers', 'lastSync', 'syncStats'));
    }

    public function sync(Request $request)
    {
        $request->validate([
            'provider_id' => 'nullable|exists:api_providers,id',
        ]);

        $providerId = $request->input('provider_id');

        // Determine which providers to sync
        if ($providerId) {
            $providers = ApiProvider::where('id', $providerId)->where('is_active', true)->get();
        } else {
            // Sync all active providers
            $providers = ApiProvider::where('is_active', true)->get();
        }

        $totalSynced = 0;
        $errors = [];

        foreach ($providers as $provider) {
            if (!ProviderSyncFactory::supportsSync($provider->code)) {
                $errors[] = "{$provider->name}: belum support sync";
                continue;
            }

            try {
                $service       = ProviderSyncFactory::resolve($provider);
                $credentials   = $provider->credentials ?? [];
                $products      = $service->getPriceList($credentials);

                $markupPct = (float) \App\Models\Setting::get('markup_percentage', 5);

                foreach ($products as $item) {
                    $sellSuggestion = $item['price'] + (($item['price'] * $markupPct) / 100);

                    // Check if already imported as a product mapping
                    $existingMapping = \App\Models\ProductProviderMapping::where('api_provider_id', $provider->id)
                        ->where('provider_product_code', $item['provider_product_code'])
                        ->first();

                    ScrapedProduct::updateOrCreate(
                        [
                            'api_provider_id'       => $provider->id,
                            'provider_product_code'  => $item['provider_product_code'],
                        ],
                        [
                            'product_name'           => $item['product_name'],
                            'brand'                  => $item['brand'] ?? null,
                            'category_name'          => $item['category_name'] ?? null,
                            'type'                   => $item['type'] ?? 'prepaid',
                            'price'                  => $item['price'],
                            'price_sell_suggestion'   => round($sellSuggestion, 2),
                            'status_provider'        => $item['status_provider'] ?? 'available',
                            'is_imported'            => $existingMapping ? true : false,
                            'imported_product_id'    => $existingMapping?->product_id,
                            'synced_at'              => now(),
                        ]
                    );

                    $totalSynced++;
                }

            } catch (\Exception $e) {
                Log::error("Sync error [{$provider->name}]: " . $e->getMessage());
                $errors[] = "{$provider->name}: " . $e->getMessage();
            }
        }

        $message = "Berhasil sync {$totalSynced} produk.";
        if (!empty($errors)) {
            $message .= ' Error: ' . implode('; ', $errors);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'      => true,
                'total_synced' => $totalSynced,
                'errors'       => $errors,
                'message'      => $message,
            ]);
        }

        return back()->with($errors ? 'error' : 'success', $message);
    }
}
