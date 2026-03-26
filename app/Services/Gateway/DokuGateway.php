<?php

namespace App\Services\Gateway;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DokuGateway implements PaymentGatewayInterface
{
    public function createPayment(Transaction $transaction, array $credentials, bool $isTestMode = false): array|false
    {
        // TODO: Implement DOKU API integration when client provides credentials
        Log::info('DokuGateway::createPayment called — awaiting DOKU API implementation', [
            'invoice' => $transaction->invoice_number,
        ]);

        return false;
    }

    public function handleWebhook(Request $request, array $credentials): array
    {
        // TODO: Implement DOKU webhook handling
        return ['status' => 'pending', 'reference' => null, 'invoice' => null];
    }

    public static function requiredCredentials(): array
    {
        return [
            'client_id'  => 'Client ID',
            'secret_key' => 'Secret Key',
        ];
    }
}
