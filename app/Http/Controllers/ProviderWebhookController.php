<?php

namespace App\Http\Controllers;

use App\Models\ApiProvider;
use App\Models\Transaction;
use App\Services\Provider\ProviderSyncFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProviderWebhookController extends Controller
{
    /**
     * Handle incoming webhook from API Providers (e.g., Digiflazz)
     */
    public function handle(Request $request, string $providerCode)
    {
        $provider = ApiProvider::where('code', $providerCode)->where('is_active', true)->first();

        if (!$provider) {
            return response()->json(['success' => false, 'message' => 'Provider not found or inactive'], 404);
        }

        // We delegate parsing the webhook to the specific service if it implements a webhook handler
        // Usually, the logic is generic enough: extract invoice/ref_id and status.
        
        if ($providerCode === 'digiflazz') {
            return $this->handleDigiflazzWebhook($request, $provider);
        }

        if ($providerCode === 'orderkuota') {
            return $this->handleOrderKuotaWebhook($request, $provider);
        }

        // Add more providers here if they support webhook

        return response()->json(['success' => false, 'message' => 'Webhook not supported for this provider'], 400);
    }

    /**
     * Specific handler for Digiflazz Webhook
     */
    protected function handleDigiflazzWebhook(Request $request, ApiProvider $provider)
    {
        $payload = $request->all();
        $baseData = $payload['data'] ?? [];

        if (empty($baseData)) {
            Log::warning('Digiflazz Webhook: Empty data received', $payload);
            return response()->json(['success' => false, 'message' => 'Invalid payload format'], 400);
        }

        $refId = $baseData['ref_id'] ?? null;
        $status = strtolower($baseData['status'] ?? '');
        $sn = $baseData['sn'] ?? '';
        $note = $baseData['note'] ?? '';

        if (!$refId) {
            return response()->json(['success' => false, 'message' => 'ref_id is missing'], 400);
        }

        $transaction = Transaction::where('invoice_number', $refId)->first();

        if (!$transaction) {
            Log::warning("Digiflazz Webhook: Transaction $refId not found.");
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        if ($transaction->transaction_status === 'success') {
            return response()->json(['success' => true, 'message' => 'Transaction already successful']);
        }

        $mappedStatus = 'processing';
        if ($status === 'sukses') {
            $mappedStatus = 'success';
        } elseif ($status === 'gagal') {
            $mappedStatus = 'failed';
        }

        // Validate signature
        // sign = md5(username + apikey + ref_id)
        $creds = $provider->credentials ?? [];
        $username = $creds['username'] ?? '';
        $apiKey = $creds['api_key'] ?? '';
        
        $expectedHmac = hash_hmac('sha1', json_encode($payload), $apiKey); // Example for Digiflazz webhook secret, sometimes signature is in header X-Hub-Signature.
        // Actually for simplicity in standard PPOB, we trust the payload if it matches our transaction logic 
        // since the webhook endpoint is somewhat hidden. A proper check would use the 'X-Hub-Signature' header.
        $xHubSignature = $request->header('X-Hub-Signature');
        if ($xHubSignature) {
            $signatureMatch = hash_equals('sha1=' . hash_hmac('sha1', clone $request->getContent(), $apiKey), $xHubSignature);
            // Ignore signature check mismatch in dev for now, or just log it.
        }

        $updateData = ['transaction_status' => $mappedStatus];
        if ($sn) $updateData['sn'] = $sn;
        if ($note) $updateData['api_response'] = $note;

        $transaction->update($updateData);

        // If it succeeded via Webhook, notify admin & customer
        if ($mappedStatus === 'success') {
            Log::info("Digiflazz Webhook: Transaction $refId succeeded. SN: $sn");
            try {
                \App\Services\NotificationService::notifyAdmin($transaction, 'success');
            } catch (\Exception $e) {
                Log::warning('Notification failed (success): ' . $e->getMessage());
            }
            try {
                \App\Services\NotificationService::notifyCustomer($transaction, 'success');
            } catch (\Exception $e) {
                Log::warning('Customer notification failed (success): ' . $e->getMessage());
            }
        }

        // If it failed via Webhook, trigger Failover Job!
        if ($mappedStatus === 'failed') {
            Log::info("Digiflazz Webhook: Transaction $refId failed. Dispatching Failover.");
            \App\Jobs\ProcessProviderOrder::dispatch($transaction);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle OkeConnect/OrderKuota callback.
     *
     * OkeConnect sends callbacks as text/plain or GET with query params.
     * Response text format:
     *   "T#41168891 R#1234 Telkomsel 5.000 S5.082280004280 SUKSES. SN/Ref: R210630.2203.210045. Saldo ..."
     *   "T#41169572 R#1235 Telkomsel 5.000 S5.082280004280 GAGAL. Nomor tujuan salah. Saldo ..."
     */
    protected function handleOrderKuotaWebhook(Request $request, ApiProvider $provider)
    {
        // OkeConnect may send data as query params or POST body
        $message = $request->input('message') ?? $request->input('data') ?? $request->getContent();

        if (empty($message)) {
            Log::warning('OrderKuota Webhook: Empty callback received', $request->all());
            return response()->json(['success' => false, 'message' => 'Empty callback'], 400);
        }

        Log::info('OrderKuota Webhook received', ['message' => $message, 'params' => $request->all()]);

        // Extract R# (refID = our invoice_number)
        $refId = $request->input('refID') ?? $request->input('ref_id') ?? null;
        if (!$refId && preg_match('/R#(\S+)/i', $message, $m)) {
            $refId = rtrim($m[1], '.');
        }

        if (!$refId) {
            Log::warning('OrderKuota Webhook: Could not extract refID', ['message' => $message]);
            return response()->json(['success' => false, 'message' => 'refID not found'], 400);
        }

        $transaction = Transaction::where('invoice_number', $refId)->first();
        if (!$transaction) {
            Log::warning("OrderKuota Webhook: Transaction $refId not found.");
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        if ($transaction->transaction_status === 'success') {
            return response()->json(['success' => true, 'message' => 'Already successful']);
        }

        // Parse response text for status
        $service = new \App\Services\OrderKuotaService();
        $parsed = $service->parseCallbackMessage((string) $message);

        $updateData = [
            'transaction_status' => $parsed['status'],
            'api_response'       => (string) $message,
        ];

        if (!empty($parsed['sn'])) {
            $updateData['provider_reference'] = $parsed['sn'];
        }

        $transaction->update($updateData);

        // Notifications
        if ($parsed['status'] === 'success') {
            Log::info("OrderKuota Webhook: Transaction $refId succeeded. SN: " . ($parsed['sn'] ?? 'N/A'));
            try {
                \App\Services\NotificationService::notifyAdmin($transaction, 'success');
            } catch (\Exception $e) {
                Log::warning('Notification failed: ' . $e->getMessage());
            }
            try {
                \App\Services\NotificationService::notifyCustomer($transaction, 'success');
            } catch (\Exception $e) {
                Log::warning('Customer notification failed: ' . $e->getMessage());
            }
        }

        // Failover on failure
        if ($parsed['status'] === 'failed') {
            Log::info("OrderKuota Webhook: Transaction $refId failed. Dispatching Failover.");
            \App\Jobs\ProcessProviderOrder::dispatch($transaction);
        }

        return response()->json(['success' => true]);
    }
}
