<?php

namespace App\Services\Gateway;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KlikQRISGateway implements PaymentGatewayInterface
{
    /**
     * Create a payment request to the gateway.
     */
    public function createPayment(Transaction $transaction, array $credentials, bool $isTestMode = false): array|false
    {
        $apiKey = $credentials['api_key'] ?? '';
        $merchantId = $credentials['merchant_id'] ?? '';

        if (!$apiKey || !$merchantId) {
            Log::error('KlikQRIS: Missing API Key or Merchant ID');
            return false;
        }

        $url = 'https://klikqris.com/api/qrisv2/create';

        $amountInt = (int) round($transaction->total_amount);
        
        if ($amountInt < 1000) {
            Log::error('KlikQRIS: Amount terlalu kecil', ['amount' => $amountInt, 'invoice' => $transaction->invoice_number]);
            return false;
        }

        $payload = [
            'order_id'     => $transaction->invoice_number,
            'id_merchant'  => $merchantId,
            'amount'       => $amountInt,
            'keterangan'   => 'Pembayaran Trx ' . $transaction->invoice_number,
            'via'          => 'Web',
            'callback_url' => route('webhook.pg', ['gatewayCode' => 'klikqris']),
            'return_url'   => route('transaction.show', $transaction->invoice_number),
        ];

        try {
            $response = Http::withHeaders([
                'x-api-key'   => $apiKey,
                'id_merchant' => $merchantId,
                'Accept'      => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === true) {
                    $resData = $data['data'] ?? [];
                    return [
                        'checkout_url' => $resData['direct_url'] ?? null,
                        'reference'    => $resData['order_id'] ?? $transaction->invoice_number,
                        'qr_url'       => $resData['qris_url'] ?? null,
                        'qr_image'     => $resData['qris_image'] ?? null,
                        'total_amount' => $resData['total_amount'] ?? null,
                        'expired_at'   => $resData['expired_at'] ?? null,
                    ];
                }
            }
            
            $errBody = $response->json() ?? ['raw' => $response->body()];
            Log::error('KlikQRIS Create Payment Failed', ['response' => $errBody, 'amount' => $amountInt, 'invoice' => $transaction->invoice_number]);
            return false;

        } catch (\Exception $e) {
            Log::error('KlikQRIS Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle incoming webhook/callback from the gateway.
     */
    public function handleWebhook(Request $request, array $credentials): array
    {
        $payload = $request->all();
        
        $statusStr     = $payload['status'] ?? '';
        $data          = $payload['data'] ?? [];
        $orderId       = $data['order_id'] ?? null;
        $paymentStatus = $data['status'] ?? '';

        if (!$orderId) {
            return ['status' => 'invalid', 'invoice' => ''];
        }

        $internalStatus = 'pending';
        
        if (strtolower($statusStr) === 'success' || strtoupper($paymentStatus) === 'PAID') {
            $internalStatus = 'paid';
        }

        return [
            'status'    => $internalStatus,
            'invoice'   => $orderId,
            'reference' => $orderId,
        ];
    }

    /**
     * Get the list of credential keys this gateway needs.
     */
    public static function requiredCredentials(): array
    {
        return [
            'api_key'     => 'API Key',
            'merchant_id' => 'Merchant ID',
        ];
    }
}
