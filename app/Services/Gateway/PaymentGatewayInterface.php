<?php

namespace App\Services\Gateway;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

interface PaymentGatewayInterface
{
    /**
     * Create a payment request to the gateway.
     *
     * @param Transaction $transaction
     * @param array $credentials Decrypted credentials from DB
     * @param bool $isTestMode
     * @return array|false  Returns payment data (checkout_url, reference, etc.) or false on failure
     */
    public function createPayment(Transaction $transaction, array $credentials, bool $isTestMode = false): array|false;

    /**
     * Handle incoming webhook/callback from the gateway.
     *
     * @param Request $request
     * @param array $credentials Decrypted credentials from DB
     * @return array  ['status' => 'paid'|'failed'|'pending', 'reference' => '...', 'invoice' => '...']
     */
    public function handleWebhook(Request $request, array $credentials): array;

    /**
     * Get the list of credential keys this gateway needs.
     * Used by admin UI to show the right fields.
     *
     * @return array  e.g. ['api_key' => 'API Key', 'secret_key' => 'Secret Key']
     */
    public static function requiredCredentials(): array;
}
