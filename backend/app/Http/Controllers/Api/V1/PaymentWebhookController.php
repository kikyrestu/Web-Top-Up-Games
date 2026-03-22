<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Payment\Services\PaymentWebhookService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PaymentWebhookController extends Controller
{
    public function __construct(private readonly PaymentWebhookService $paymentWebhookService)
    {
    }

    public function handle(Request $request, string $gateway): JsonResponse
    {
        $payload = $request->all();
        $payload['gateway'] = strtoupper($gateway);

        /** @var array<string, string> $headers */
        $headers = collect($request->headers->all())
            ->map(static fn (array $values) => (string) ($values[0] ?? ''))
            ->all();

        $result = $this->paymentWebhookService->handle($payload, $headers);

        $isSuccess = (bool) ($result['verified'] ?? false) && (bool) ($result['processed'] ?? false || $result['duplicate'] ?? false);

        return response()->json([
            'success' => $isSuccess,
            'code' => (string) ($result['code'] ?? 'WEBHOOK_UNKNOWN'),
            'message' => $isSuccess
                ? 'Webhook accepted'
                : 'Webhook rejected',
            'data' => [
                'verified' => (bool) ($result['verified'] ?? false),
                'duplicate' => (bool) ($result['duplicate'] ?? false),
                'processed' => (bool) ($result['processed'] ?? false),
            ],
        ], $isSuccess ? 200 : 422);
    }
}
