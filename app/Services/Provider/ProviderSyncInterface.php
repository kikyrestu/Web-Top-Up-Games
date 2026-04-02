<?php

namespace App\Services\Provider;

interface ProviderSyncInterface
{
    /**
     * Get the full price list from the provider API.
     *
     * @param  array  $credentials  Provider API credentials
     * @param  array  $options      Extra options (e.g. ['cmd' => 'prepaid'])
     * @return array  Array of products, each with keys:
     *                - provider_product_code (string)
     *                - product_name (string)
     *                - brand (string|null)
     *                - category_name (string|null)
     *                - type (string: prepaid|pasca)
     *                - price (float: harga modal)
     *                - status_provider (string: available|disturb|empty)
     */
    public function getPriceList(array $credentials, array $options = []): array;

    /**
     * Create a transaction order to the provider.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return array|bool Array data on success, or false on failed/error
     */
    public function createTransaction(\App\Models\Transaction $transaction);

    /**
     * Check transaction status on the provider.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return string Updated status ('processing', 'success', 'failed')
     */
    public function checkStatus(\App\Models\Transaction $transaction): string;
}
