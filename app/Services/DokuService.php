<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DokuService
{
    protected $clientId;
    protected $secretKey;
    protected $isTestMode;

    public function __construct()
    {
        $gateway = PaymentGateway::where('code', 'doku')->first();
        if ($gateway && $gateway->credentials) {
            $this->clientId = $gateway->credentials['client_id'] ?? '';
            $this->secretKey = $gateway->credentials['secret_key'] ?? '';
            $this->isTestMode = $gateway->is_test_mode;
        }
    }

    public function requestTransaction(Transaction $transaction)
    {
        $url = $this->isTestMode 
            ? 'https://api-sandbox.doku.com/checkout/v1/payment'
            : 'https://api.doku.com/checkout/v1/payment';

        // Signature Generation for DOKU
        $requestId = uniqid();
        $targetPath = parse_url($url, PHP_URL_PATH);
        $timestamp = gmdate("Y-m-d\TH:i:s\Z");

        $payload = [
            'order' => [
                'amount' => (int) $transaction->total_amount,
                'invoice_number' => $transaction->invoice_number,
                'callback_url' => route('transaction.show', $transaction->invoice_number),
            ],
            'customer' => [
                'id' => uniqid(),
                'name' => $transaction->customer_name ?? 'Guest',
                'email' => filter_var($transaction->customer_name, FILTER_VALIDATE_EMAIL) ? $transaction->customer_name : 'guest@example.com',
            ],
        ];

        $digest = base64_encode(hash('sha256', json_encode($payload), true));
        $rawSignature = "Client-Id:" . $this->clientId . "\n" .
                        "Request-Id:" . $requestId . "\n" .
                        "Request-Timestamp:" . $timestamp . "\n" .
                        "Request-Target:" . $targetPath . "\n" .
                        "Digest:" . $digest;

        $signature = base64_encode(hash_hmac('sha256', $rawSignature, $this->secretKey, true));

        try {
            $response = Http::withHeaders([
                'Client-Id' => $this->clientId,
                'Request-Id' => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature' => "HMACSHA256=" . $signature,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['response']['payment']['url'])) {
                return [
                    'reference' => $result['message']['id'] ?? null,
                    'redirect_url' => $result['response']['payment']['url'],
                    'success' => true
                ];
            }

            Log::error('Doku Request Failed:', ['response' => $result, 'data' => $payload]);
            return false;

        } catch (\Exception $e) {
            Log::error('Doku Exception:', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
