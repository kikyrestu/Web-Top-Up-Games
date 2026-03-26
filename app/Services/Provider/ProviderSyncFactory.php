<?php

namespace App\Services\Provider;

use App\Models\ApiProvider;

class ProviderSyncFactory
{
    /**
     * Map provider code → service class
     */
    protected static array $drivers = [
        'digiflazz' => \App\Services\DigiflazzService::class,
        'rajabiller' => \App\Services\RajabillerService::class,
        'orderkuota' => \App\Services\OrderKuotaService::class,
    ];

    /**
     * Resolve a sync-capable service for the given provider.
     */
    public static function resolve(ApiProvider $provider): ProviderSyncInterface
    {
        $code = strtolower($provider->code);

        if (!isset(self::$drivers[$code])) {
            throw new \RuntimeException("Provider [{$code}] belum support fitur sync produk.");
        }

        $serviceClass = self::$drivers[$code];
        $service = app($serviceClass);

        if (!$service instanceof ProviderSyncInterface) {
            throw new \RuntimeException("Service [{$serviceClass}] belum implement ProviderSyncInterface.");
        }

        return $service;
    }

    /**
     * Check if a provider code has sync support.
     */
    public static function supportsSync(string $code): bool
    {
        return isset(self::$drivers[strtolower($code)]);
    }
}
