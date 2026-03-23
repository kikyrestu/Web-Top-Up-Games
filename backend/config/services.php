<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'digiflazz' => [
        'base_url' => env('DIGIFLAZZ_BASE_URL', 'https://api.digiflazz.com/v1'),
        'username' => env('DIGIFLAZZ_USERNAME'),
        'api_key' => env('DIGIFLAZZ_API_KEY'),
    ],

    'rajabiller' => [
        'base_url' => env('RAJABILLER_BASE_URL'),
        'username' => env('RAJABILLER_USERNAME'),
        'api_key' => env('RAJABILLER_API_KEY'),
    ],

    'orderkuota' => [
        'base_url' => env('ORDERKUOTA_BASE_URL'),
        'username' => env('ORDERKUOTA_USERNAME'),
        'api_key' => env('ORDERKUOTA_API_KEY'),
    ],

    'provider_router' => [
        'max_retries_per_provider' => env('PROVIDER_MAX_RETRIES_PER_PROVIDER', 1),
        'circuit_breaker_enabled' => env('PROVIDER_CIRCUIT_BREAKER_ENABLED', true),
        'circuit_breaker_failure_threshold' => env('PROVIDER_CIRCUIT_BREAKER_FAILURE_THRESHOLD', 3),
        'circuit_breaker_cooldown_seconds' => env('PROVIDER_CIRCUIT_BREAKER_COOLDOWN_SECONDS', 120),
    ],

    'payment_gateways' => [
        'KLIKQRISS' => [
            'base_url' => env('KLIKQRISS_BASE_URL'),
            'api_key' => env('KLIKQRISS_API_KEY'),
            'invoice_path' => env('KLIKQRISS_INVOICE_PATH', '/invoice'),
            'expiry_minutes' => env('KLIKQRISS_EXPIRY_MINUTES', 15),
            'webhook_secret' => env('KLIKQRISS_WEBHOOK_SECRET'),
        ],
        'MIDTRANS' => [
            'base_url' => env('MIDTRANS_BASE_URL'),
            'api_key' => env('MIDTRANS_API_KEY'),
            'invoice_path' => env('MIDTRANS_INVOICE_PATH', '/invoice'),
            'expiry_minutes' => env('MIDTRANS_EXPIRY_MINUTES', 15),
            'webhook_secret' => env('MIDTRANS_WEBHOOK_SECRET'),
        ],
        'DUITKU' => [
            'base_url' => env('DUITKU_BASE_URL'),
            'api_key' => env('DUITKU_API_KEY'),
            'invoice_path' => env('DUITKU_INVOICE_PATH', '/invoice'),
            'expiry_minutes' => env('DUITKU_EXPIRY_MINUTES', 15),
            'webhook_secret' => env('DUITKU_WEBHOOK_SECRET'),
        ],
    ],

    'payment_webhook' => [
        'allowed_drift_seconds' => env('PAYMENT_WEBHOOK_ALLOWED_DRIFT_SECONDS', 300),
    ],

];
