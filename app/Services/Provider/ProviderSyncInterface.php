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
}
