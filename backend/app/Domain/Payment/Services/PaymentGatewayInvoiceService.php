<?php

declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Throwable;

final class PaymentGatewayInvoiceService
{
    /**
     * @return array{pay_url: string, expired_at: string, provider_reference: string|null, raw: array<string, mixed>}
     */
    public function createInvoice(Order $order, string $gateway, string $gatewayReference, float $amount, ?string $method = null): array
    {
        $gatewayCode = strtoupper($gateway);
        $baseUrl = rtrim((string) config('services.payment_gateways.'.$gatewayCode.'.base_url', ''), '/');
        $apiKey = (string) config('services.payment_gateways.'.$gatewayCode.'.api_key', '');
        $invoicePath = (string) config('services.payment_gateways.'.$gatewayCode.'.invoice_path', '/invoice');
        $expiryMinutes = max(5, (int) config('services.payment_gateways.'.$gatewayCode.'.expiry_minutes', 15));

        if ($baseUrl === '' || $apiKey === '') {
            return $this->fallbackInvoice($gatewayCode, $gatewayReference, $expiryMinutes);
        }

        $requestPayload = [
            'order_code' => $order->order_code,
            'gateway_reference' => $gatewayReference,
            'amount' => $amount,
            'method' => $method,
            'customer_target' => $order->customer_target,
            'callback_url' => config('app.url').'/api/v1/payments/webhook/'.strtolower($gatewayCode),
            'expiry_minutes' => $expiryMinutes,
        ];

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                ])
                ->post($baseUrl.$invoicePath, $requestPayload);
        } catch (Throwable $exception) {
            return $this->fallbackInvoice($gatewayCode, $gatewayReference, $expiryMinutes, [
                'error' => 'network_exception',
                'message' => $exception->getMessage(),
            ]);
        }

        if (!$response->successful()) {
            return $this->fallbackInvoice($gatewayCode, $gatewayReference, $expiryMinutes, [
                'http_status' => $response->status(),
            ]);
        }

        $json = $response->json();
        $data = is_array($json) ? Arr::get($json, 'data', []) : [];

        $payUrl = (string) (Arr::get($data, 'pay_url')
            ?? Arr::get($data, 'payment_url')
            ?? Arr::get($data, 'invoice_url')
            ?? '');

        if ($payUrl === '') {
            return $this->fallbackInvoice($gatewayCode, $gatewayReference, $expiryMinutes, [
                'error' => 'missing_pay_url_in_gateway_response',
                'response' => is_array($json) ? $json : [],
            ]);
        }

        $expiredAt = (string) (Arr::get($data, 'expired_at') ?? now()->addMinutes($expiryMinutes)->toISOString());
        $providerReference = Arr::get($data, 'provider_reference') ?? Arr::get($data, 'invoice_id');

        return [
            'pay_url' => $payUrl,
            'expired_at' => $expiredAt,
            'provider_reference' => is_string($providerReference) ? $providerReference : null,
            'raw' => is_array($json) ? $json : [],
        ];
    }

    /**
     * @param array<string, mixed> $extra
     * @return array{pay_url: string, expired_at: string, provider_reference: string|null, raw: array<string, mixed>}
     */
    private function fallbackInvoice(string $gatewayCode, string $gatewayReference, int $expiryMinutes, array $extra = []): array
    {
        return [
            'pay_url' => rtrim((string) config('app.url'), '/').'/pay/'.strtolower($gatewayCode).'/'.$gatewayReference,
            'expired_at' => now()->addMinutes($expiryMinutes)->toISOString(),
            'provider_reference' => null,
            'raw' => array_merge([
                'source' => 'fallback',
            ], $extra),
        ];
    }
}
