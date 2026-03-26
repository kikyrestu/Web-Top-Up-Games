<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected $serverKey;
    protected $isTestMode;

    public function __construct()
    {
        $gateway = PaymentGateway::where('code', 'midtrans')->first();
        if ($gateway && $gateway->credentials) {
            $this->serverKey = $gateway->credentials['server_key'] ?? '';
            $this->isTestMode = $gateway->is_test_mode;
        }
    }

    public function requestTransaction(Transaction $transaction)
    {
        $url = $this->isTestMode 
            ? 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            : 'https://app.midtrans.com/snap/v1/transactions';

        $orderItems = [];
        foreach ($transaction->items as $item) {
            $orderItems[] = [
                'id'       => $item->product_id,
                'price'    => (int) $item->price_sell,
                'quantity' => (int) $item->quantity,
                'name'     => substr($item->product_name, 0, 50),
            ];
        }

        if ($transaction->fee_amount > 0) {
            $orderItems[] = [
                'id'       => 'FEE',
                'price'    => (int) $transaction->fee_amount,
                'quantity' => 1,
                'name'     => 'Biaya Admin',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id'     => $transaction->invoice_number,
                'gross_amount' => (int) $transaction->total_amount,
            ],
            'customer_details' => [
                'first_name' => filter_var($transaction->customer_name, FILTER_VALIDATE_EMAIL) ? 'Guest' : ($transaction->customer_name ?? 'Guest'),
                'email'      => filter_var($transaction->customer_name, FILTER_VALIDATE_EMAIL) ? $transaction->customer_name : 'guest@example.com',
                'phone'      => $transaction->customer_contact ?? '08123456789',
            ],
            'item_details' => $orderItems,
        ];

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post($url, $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['token'])) {
                return [
                    'reference' => $result['token'], // Midtrans token
                    'redirect_url' => $result['redirect_url'],
                    'success' => true
                ];
            }

            Log::error('Midtrans Request Failed:', ['response' => $result, 'data' => $payload]);
            return false;

        } catch (\Exception $e) {
            Log::error('Midtrans Exception:', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
