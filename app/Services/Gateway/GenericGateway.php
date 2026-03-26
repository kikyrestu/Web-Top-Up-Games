<?php

namespace App\Services\Gateway;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Fallback gateway for unknown/unconfigured gateway codes.
 * Client can extend via PaymentGatewayFactory::extend().
 */
class GenericGateway implements PaymentGatewayInterface
{
    public function createPayment(Transaction $transaction, array $credentials, bool $isTestMode = false): array|false
    {
        Log::warning('GenericGateway::createPayment — no driver configured for this gateway', [
            'invoice' => $transaction->invoice_number,
        ]);

        return false;
    }

    public function handleWebhook(Request $request, array $credentials): array
    {
        Log::warning('GenericGateway::handleWebhook — no driver configured');
        return ['status' => 'pending', 'reference' => null, 'invoice' => null];
    }

    public static function requiredCredentials(): array
    {
        return [
            'api_key'    => 'API Key',
            'secret_key' => 'Secret Key',
        ];
    }
}
