<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KlikQrisService
{
    protected $apiKey;
    protected $merchantId;
    protected $isTestMode;

    public function __construct()
    {
        $gateway = PaymentGateway::where('code', 'klikqris')->first();
        if ($gateway && $gateway->credentials) {
            $this->apiKey = $gateway->credentials['api_key'] ?? '';
            $this->merchantId = $gateway->credentials['merchant_id'] ?? '';
            $this->isTestMode = $gateway->is_test_mode;
        }
    }

    public function requestTransaction(Transaction $transaction)
    {
        $url = 'https://api.klikqris.id/api/v1/qris/request'; // as a boilerplate

        $payload = [
            'merchant_id'  => $this->merchantId,
            'api_key'      => $this->apiKey,
            'invoice_id'   => $transaction->invoice_number,
            'amount'       => (int) $transaction->total_amount,
            'customer_name'=> $transaction->customer_name ?? 'Guest',
            'customer_phone'=> $transaction->customer_contact ?? '08123456789',
        ];

        try {
            $response = Http::post($url, $payload);
            $result = $response->json();

            if ($response->successful() && isset($result['success']) && $result['success'] == true) {
                return [
                    'reference' => $result['data']['reference'] ?? null,
                    'qr_image'  => $result['data']['qr_image'] ?? null,
                    'success'   => true
                ];
            }

            Log::error('KlikQRIS Request Failed:', ['response' => $result, 'data' => $payload]);
            return false;

        } catch (\Exception $e) {
            Log::error('KlikQRIS Exception:', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
