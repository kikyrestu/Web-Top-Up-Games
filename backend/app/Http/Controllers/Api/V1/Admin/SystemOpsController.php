<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Audit\Services\AuditLogService;
use App\Domain\Catalog\Services\ProductSyncService;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProviderAttempt;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\ProviderHealthCheck;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

final class SystemOpsController extends Controller
{
    public function __construct(
        private readonly ProductSyncService $productSyncService,
        private readonly AuditLogService $auditLogService,
    ) {
    }

    public function syncProviders(): JsonResponse
    {
        $updated = $this->productSyncService->syncAll();

        $this->auditLogService->write([
            'event_type' => 'ADMIN_PROVIDER_SYNC_TRIGGERED',
            'actor_type' => 'USER',
            'actor_id' => auth()->id(),
            'entity_type' => 'SYSTEM',
            'entity_id' => null,
            'payload' => [
                'updated_rows' => $updated,
            ],
            'occurred_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'code' => 'PROVIDER_SYNC_COMPLETED',
            'message' => 'Provider product sync completed',
            'data' => [
                'updated_rows' => $updated,
            ],
        ]);
    }

    public function dashboardOverview(): JsonResponse
    {
        $failedOrders = Order::query()->where('status', 'FAILED')->count();
        $processingOrders = Order::query()->where('status', 'PROCESSING')->count();
        $pendingOrders = Order::query()->where('status', 'PENDING')->count();
        $pendingPayments = Payment::query()->where('status', 'UNPAID')->count();

        $providers = Provider::query()
            ->select(['id', 'code', 'name', 'is_active', 'updated_at'])
            ->orderBy('code')
            ->get()
            ->map(static fn (Provider $provider): array => [
                'id' => (int) $provider->id,
                'code' => (string) $provider->code,
                'name' => (string) $provider->name,
                'is_active' => (bool) $provider->is_active,
                'updated_at' => $provider->updated_at,
            ]);

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_DASHBOARD_OVERVIEW',
            'message' => 'Dashboard overview loaded',
            'data' => [
                'orders' => [
                    'failed' => $failedOrders,
                    'processing' => $processingOrders,
                    'pending' => $pendingOrders,
                ],
                'payments' => [
                    'unpaid' => $pendingPayments,
                ],
                'providers' => $providers,
            ],
        ]);
    }

    public function dashboardMetrics(): JsonResponse
    {
        $hours = (int) request()->integer('hours', 24);
        $hours = max(1, min($hours, 168));

        $from = now()->subHours($hours);

        $providerAttempts = OrderProviderAttempt::query()
            ->with('provider:id,code,name')
            ->where('attempted_at', '>=', $from)
            ->get();

        $providerRows = $providerAttempts
            ->groupBy('provider_id')
            ->map(function (Collection $attempts) use ($from): array {
                $provider = $attempts->first()?->provider;
                $total = $attempts->count();
                $successCount = $attempts->whereIn('status', ['SUCCESS', 'PAID'])->count();
                $failed = $attempts->whereIn('status', ['FAILED', 'ERROR'])->count();
                $skipped = $attempts->where('status', 'SKIPPED')->count();

                $failReasons = $attempts
                    ->filter(static fn (OrderProviderAttempt $attempt): bool => in_array((string) $attempt->status, ['FAILED', 'ERROR'], true))
                    ->map(function (OrderProviderAttempt $attempt): string {
                        $payload = is_array($attempt->response_payload) ? $attempt->response_payload : [];
                        $raw = is_array($payload['raw'] ?? null) ? $payload['raw'] : [];

                        return (string) ($raw['error'] ?? $raw['message'] ?? 'unknown_error');
                    })
                    ->countBy()
                    ->sortDesc()
                    ->take(3)
                    ->map(static fn (int $count, string $reason): array => [
                        'reason' => $reason,
                        'count' => $count,
                    ])
                    ->values()
                    ->all();

                $latencySamples = ProviderHealthCheck::query()
                    ->where('provider_id', (int) $attempts->first()->provider_id)
                    ->whereNotNull('response_time_ms')
                    ->where('checked_at', '>=', $from)
                    ->pluck('response_time_ms')
                    ->map(static fn ($value): int => (int) $value)
                    ->filter(static fn (int $value): bool => $value > 0)
                    ->values();

                return [
                    'provider_id' => (int) $attempts->first()->provider_id,
                    'provider_code' => (string) ($provider?->code ?? 'UNKNOWN'),
                    'provider_name' => (string) ($provider?->name ?? 'Unknown Provider'),
                    'attempts' => $total,
                    'success_count' => $successCount,
                    'failed_count' => $failed,
                    'skipped_count' => $skipped,
                    'success_rate_pct' => $this->percentage($successCount, $total),
                    'p95_latency_ms' => $this->percentile($latencySamples, 95),
                    'top_fail_reasons' => $failReasons,
                ];
            })
            ->sortByDesc('attempts')
            ->values();

        $payments = Payment::query()
            ->where('created_at', '>=', $from)
            ->get(['gateway', 'status']);

        $paymentRows = $payments
            ->groupBy(static fn (Payment $payment): string => strtoupper((string) $payment->gateway))
            ->map(function (Collection $rows, string $gateway): array {
                $total = $rows->count();
                $paid = $rows->where('status', 'PAID')->count();
                $failed = $rows->whereIn('status', ['FAILED', 'EXPIRED'])->count();
                $unpaid = $rows->where('status', 'UNPAID')->count();

                return [
                    'gateway' => $gateway,
                    'total' => $total,
                    'paid_count' => $paid,
                    'failed_count' => $failed,
                    'unpaid_count' => $unpaid,
                    'paid_rate_pct' => $this->percentage($paid, $total),
                ];
            })
            ->sortByDesc('total')
            ->values();

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_DASHBOARD_METRICS',
            'message' => 'Dashboard metrics loaded',
            'data' => [
                'window_hours' => $hours,
                'generated_at' => now()->toISOString(),
                'providers' => $providerRows,
                'payments' => $paymentRows,
            ],
        ]);
    }

    private function percentage(int $success, int $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(($success / $total) * 100, 2);
    }

    /**
     * @param Collection<int, int> $samples
     */
    private function percentile(Collection $samples, int $percentile): ?int
    {
        if ($samples->isEmpty()) {
            return null;
        }

        $sorted = $samples->sort()->values();
        $index = (int) ceil(($percentile / 100) * $sorted->count()) - 1;
        $index = max(0, min($index, $sorted->count() - 1));

        return (int) $sorted->get($index);
    }
}
