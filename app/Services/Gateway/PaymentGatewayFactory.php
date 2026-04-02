<?php

namespace App\Services\Gateway;

use App\Models\PaymentGateway;

class PaymentGatewayFactory
{
    /**
     * Map of gateway codes to their driver classes.
     */
    protected static array $drivers = [
        'midtrans'  => MidtransGateway::class,
        'xendit'    => XenditGateway::class,
        'doku'      => DokuGateway::class,
        'klikqris'  => KlikQRISGateway::class,
    ];

    /**
     * Resolve a gateway driver instance by PaymentGateway model.
     */
    public static function resolve(PaymentGateway $gateway): PaymentGatewayInterface
    {
        $code = strtolower($gateway->code);

        if (isset(self::$drivers[$code])) {
            return new self::$drivers[$code]();
        }

        // Fallback: generic stub
        return new GenericGateway();
    }

    /**
     * Check if a specific gateway code has a driver.
     */
    public static function hasDriver(string $code): bool
    {
        return isset(self::$drivers[strtolower($code)]);
    }

    /**
     * Register a custom driver at runtime.
     */
    public static function extend(string $code, string $driverClass): void
    {
        self::$drivers[strtolower($code)] = $driverClass;
    }

    /**
     * Get all registered driver codes.
     */
    public static function availableDrivers(): array
    {
        return array_keys(self::$drivers);
    }
}
