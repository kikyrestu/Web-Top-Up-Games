<?php

namespace App\Jobs;

use App\Models\ApiProvider;
use App\Models\ProductProviderMapping;
use App\Models\Transaction;
use App\Services\Provider\ProviderSyncFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessProviderOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh transaction to ensure latest status
        $this->transaction->refresh();

        if ($this->transaction->transaction_status === 'success' || $this->transaction->transaction_status === 'failed') {
            return; // Already processed
        }

        $item = $this->transaction->items()->first();
        if (!$item) {
            Log::error("ProcessProviderOrder Job Failed: No items found for Trx {$this->transaction->invoice_number}");
            return;
        }

        $apiProviderId = $item->api_provider_id;
        $provider = ApiProvider::find($apiProviderId);

        if (!$provider || !$provider->is_active) {
            Log::warning("ProcessProviderOrder: Provider is inactive or deleted for Trx {$this->transaction->invoice_number}");
            $this->handleFailover();
            return;
        }

        try {
            if (!ProviderSyncFactory::supportsSync($provider->code)) {
                Log::error("ProcessProviderOrder: Provider {$provider->code} not supported");
                $this->handleFailover();
                return;
            }

            $service = ProviderSyncFactory::resolve($provider);
            
            // For postpaid with inquiry_ref, use payPostpaid method
            $inquiryRef = $this->transaction->inquiry_ref;
            if ($inquiryRef && method_exists($service, 'payPostpaid')) {
                $response = $service->payPostpaid($this->transaction, $inquiryRef);
            } else {
                // Execute regular prepaid order
                $response = $service->createTransaction($this->transaction);
            }

            // Fetch the updated transaction status
            $this->transaction->refresh();

            if ($this->transaction->transaction_status === 'failed') {
                Log::warning("Provider {$provider->code} returned failed status for Trx {$this->transaction->invoice_number}. Attempting Failover.");
                $this->handleFailover();
            }

        } catch (\Exception $e) {
            Log::error("ProcessProviderOrder Exception: {$e->getMessage()} on Trx {$this->transaction->invoice_number}");
            $this->handleFailover();
        }
    }

    /**
     * Handle failover logic to alternative providers.
     */
    protected function handleFailover(): void
    {
        $this->transaction->refresh();
        $item = $this->transaction->items()->first();
        if (!$item) return;

        // Skip if already success somehow
        if ($this->transaction->transaction_status === 'success') return;

        $currentProviderId = $item->api_provider_id;
        $productId = $item->product_id;

        Log::info("Failover invoked for Trx {$this->transaction->invoice_number}. Finding alternatives for product_id {$productId} (excluding provider_id {$currentProviderId}).");

        // Find alternative active mappings for this product
        $alternatives = ProductProviderMapping::where('product_id', $productId)
            ->where('api_provider_id', '!=', $currentProviderId)
            ->whereHas('apiProvider', fn($q) => $q->where('is_active', true))
            ->orderBy('price_capital', 'asc')
            ->get();

        if ($alternatives->isEmpty()) {
            Log::warning("Failover aborted: No alternative active providers found for Trx {$this->transaction->invoice_number}. Marking as Failed.");
            
            // Cannot be processed anymore
            if ($this->transaction->transaction_status !== 'failed') {
                $this->transaction->update(['transaction_status' => 'failed', 'api_response' => 'Failed by all providers/No failover available.']);
                
                // If it was a paid transaction (not TOPUP), admin should handle refund via Notification
                \App\Services\NotificationService::notifyAdmin($this->transaction, 'failed');
            }
            return;
        }

        // Pop the cheapest alternative
        $selectedMapping = $alternatives->first();

        Log::info("Failover matched! Switching Trx {$this->transaction->invoice_number} to Provider ID {$selectedMapping->api_provider_id}.");

        // Update item details to the new provider mapping
        $newCapital = (float) $selectedMapping->price_capital;
        $sellPrice = (float) $item->price_sell;
        $qty = $item->quantity;
        
        $item->update([
            'api_provider_id' => $selectedMapping->api_provider_id,
            'provider_product_code' => $selectedMapping->provider_product_code,
            'price_capital' => $newCapital,
            'commission_amount' => max(0, ($sellPrice - $newCapital) * $qty),
        ]);

        // Re-dispatch the job to try again using the new provider
        ProcessProviderOrder::dispatch($this->transaction)->delay(now()->addSeconds(5));
    }
}
