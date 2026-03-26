<?php

namespace App\Services\Gateway;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditGateway implements PaymentGatewayInterface
{
    public function createPayment(Transaction $transaction, array $credentials, bool $isTestMode = false): array|false
    {
        $secretKey = $credentials['secret_key'] ?? '';
        $baseUrl   = 'https://api.xendit.co/v2/invoices';

        $payload = [
            'external_id'  => $transaction->invoice_number,
            'amount'        => (int) $transaction->total_amount,
            'payer_email'   => $transaction->customer_email ?? 'guest@example.com',
            'description'   => 'Pembayaran ' . $transaction->invoice_number,
            'currency'      => 'IDR',
            'invoice_duration' => 86400, // 24 hours
        ];

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->post($baseUrl, $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['invoice_url'])) {
                return [
                    'checkout_url' => $result['invoice_url'],
                    'reference'    => $result['id'] ?? null,
                ];
            }

            Log::error('Xendit createPayment failed', ['response' => $result]);
            return false;

        } catch (\Exception $e) {
            Log::error('Xendit exception: ' . $e->getMessage());
            return false;
        }
    }

    public function handleWebhook(Request $request, array $credentials): array
    {
        $callbackToken = $credentials['callback_token'] ?? '';
        $headerToken   = $request->header('x-callback-token', '');

        if ($callbackToken && $headerToken !== $callbackToken) {
            return ['status' => 'invalid', 'reference' => null, 'invoice' => null];
        }

        $data   = $request->all();
        $status = match (strtolower($data['status'] ?? '')) {
            'paid', 'settled' => 'paid',
            'expired'         => 'failed',
            default           => 'pending',
        };

        return [
            'status'    => $status,
            'reference' => $data['id'] ?? null,
            'invoice'   => $data['external_id'] ?? null,
        ];
    }

    public static function requiredCredentials(): array
    {
        return [
            'secret_key'     => 'Secret Key',
            'callback_token' => 'Callback Verification Token',
        ];
    }
}
