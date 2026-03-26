<?php

namespace App\Services\Gateway;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransGateway implements PaymentGatewayInterface
{
    public function createPayment(Transaction $transaction, array $credentials, bool $isTestMode = false): array|false
    {
        $serverKey = $credentials['server_key'] ?? '';
        $baseUrl   = $isTestMode
            ? 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            : 'https://app.midtrans.com/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id'     => $transaction->invoice_number,
                'gross_amount' => (int) $transaction->total_amount,
            ],
            'customer_details' => [
                'email' => $transaction->customer_email ?? 'guest@example.com',
                'phone' => $transaction->customer_whatsapp ?? '',
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->post($baseUrl, $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['redirect_url'])) {
                return [
                    'checkout_url' => $result['redirect_url'],
                    'token'        => $result['token'] ?? null,
                    'reference'    => $transaction->invoice_number,
                ];
            }

            Log::error('Midtrans createPayment failed', ['response' => $result]);
            return false;

        } catch (\Exception $e) {
            Log::error('Midtrans exception: ' . $e->getMessage());
            return false;
        }
    }

    public function handleWebhook(Request $request, array $credentials): array
    {
        $serverKey = $credentials['server_key'] ?? '';
        $data      = $request->all();

        // Verify signature
        $orderId     = $data['order_id'] ?? '';
        $statusCode  = $data['status_code'] ?? '';
        $grossAmount = $data['gross_amount'] ?? '';
        $signature   = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signature !== ($data['signature_key'] ?? '')) {
            return ['status' => 'invalid', 'reference' => null, 'invoice' => null];
        }

        $txStatus = $data['transaction_status'] ?? '';
        $fraud    = $data['fraud_status'] ?? 'accept';

        $status = match (true) {
            in_array($txStatus, ['capture', 'settlement']) && $fraud === 'accept' => 'paid',
            in_array($txStatus, ['deny', 'cancel', 'expire'])                    => 'failed',
            default                                                                => 'pending',
        };

        return [
            'status'    => $status,
            'reference' => $data['transaction_id'] ?? null,
            'invoice'   => $orderId,
        ];
    }

    public static function requiredCredentials(): array
    {
        return [
            'server_key' => 'Server Key',
            'client_key' => 'Client Key',
        ];
    }
}
