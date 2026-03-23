<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Audit\Services\AuditLogService;
use App\Domain\Catalog\Services\ProductSyncService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FileUploadLog;
use App\Models\IdempotencyRequest;
use App\Models\Order;
use App\Models\OrderProviderAttempt;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\ProviderHealthCheck;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

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
        $hours = $this->normalizedHours(request()->integer('hours', 24));
        $alertThreshold = $this->normalizedAlertThreshold(
            request()->input('alert_success_rate_threshold', config('services.dashboard.provider_success_rate_alert_threshold', 85))
        );
        $alertMinAttempts = $this->normalizedAlertMinAttempts(
            request()->input('alert_min_attempts', config('services.dashboard.provider_alert_min_attempts', 5))
        );
        $paymentAlertThreshold = $this->normalizedAlertThreshold(
            request()->input('payment_alert_paid_rate_threshold', config('services.dashboard.payment_paid_rate_alert_threshold', 75))
        );
        $paymentAlertMinTotal = $this->normalizedAlertMinAttempts(
            request()->input('payment_alert_min_total', config('services.dashboard.payment_alert_min_total', 5))
        );

        $metrics = $this->buildDashboardMetrics(
            $hours,
            $alertThreshold,
            $alertMinAttempts,
            $paymentAlertThreshold,
            $paymentAlertMinTotal
        );

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_DASHBOARD_METRICS',
            'message' => 'Dashboard metrics loaded',
            'data' => $metrics,
        ]);
    }

    public function dashboardAlerts(Request $request): JsonResponse
    {
        $hours = $this->normalizedHours($request->integer('hours', 24));
        $providerAlertThreshold = $this->normalizedAlertThreshold(
            $request->input('alert_success_rate_threshold', config('services.dashboard.provider_success_rate_alert_threshold', 85))
        );
        $providerAlertMinAttempts = $this->normalizedAlertMinAttempts(
            $request->input('alert_min_attempts', config('services.dashboard.provider_alert_min_attempts', 5))
        );
        $paymentAlertThreshold = $this->normalizedAlertThreshold(
            $request->input('payment_alert_paid_rate_threshold', config('services.dashboard.payment_paid_rate_alert_threshold', 75))
        );
        $paymentAlertMinTotal = $this->normalizedAlertMinAttempts(
            $request->input('payment_alert_min_total', config('services.dashboard.payment_alert_min_total', 5))
        );

        $metrics = $this->buildDashboardMetrics(
            $hours,
            $providerAlertThreshold,
            $providerAlertMinAttempts,
            $paymentAlertThreshold,
            $paymentAlertMinTotal
        );

        /** @var array<string, mixed> $alerts */
        $alerts = is_array($metrics['alerts'] ?? null) ? $metrics['alerts'] : [];

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_DASHBOARD_ALERTS',
            'message' => 'Dashboard alerts loaded',
            'data' => [
                'window_hours' => $hours,
                'generated_at' => $metrics['generated_at'] ?? now()->toISOString(),
                'alerts' => $alerts,
            ],
        ]);
    }

    public function dashboardHousekeeping(Request $request): JsonResponse
    {
        $hours = $this->normalizedHours($request->integer('hours', 24));
        $from = now()->subHours($hours);
        $purgeLogs = AuditLog::query()
            ->where('event_type', 'IDEMPOTENCY_PURGE_COMPLETED')
            ->where('occurred_at', '>=', $from)
            ->orderByDesc('occurred_at')
            ->get(['payload', 'occurred_at']);

        $totalDeleted = $purgeLogs->sum(static function (AuditLog $log): int {
            $payload = is_array($log->payload) ? $log->payload : [];

            return (int) ($payload['deleted_records'] ?? 0);
        });

        $lastPurge = $purgeLogs->first();

        $totalIdempotencyRecords = IdempotencyRequest::query()->count();
        $expiredIdempotencyRecords = IdempotencyRequest::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->count();

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_DASHBOARD_HOUSEKEEPING',
            'message' => 'Housekeeping summary loaded',
            'data' => [
                'window_hours' => $hours,
                'generated_at' => now()->toISOString(),
                'idempotency' => [
                    'total_records' => $totalIdempotencyRecords,
                    'expired_records' => $expiredIdempotencyRecords,
                    'purge_runs' => $purgeLogs->count(),
                    'purge_total_deleted' => $totalDeleted,
                    'purge_last_run_at' => $lastPurge?->occurred_at?->toISOString(),
                    'purge_last_deleted' => (int) ((is_array($lastPurge?->payload) ? $lastPurge?->payload['deleted_records'] ?? 0 : 0)),
                ],
            ],
        ]);
    }

    public function dashboardUploadTrend(Request $request): JsonResponse
    {
        $granularity = strtolower(trim((string) $request->query('granularity', 'hour')));
        if (!in_array($granularity, ['hour', 'day'], true)) {
            $granularity = 'hour';
        }

        $points = max(1, min((int) $request->integer('points', $granularity === 'day' ? 14 : 24), $granularity === 'day' ? 60 : 168));
        $now = now();

        if ($granularity === 'day') {
            $from = $now->copy()->startOfDay()->subDays($points - 1);
            $logs = FileUploadLog::query()
                ->where('created_at', '>=', $from)
                ->get(['created_at', 'verdict']);

            $buckets = [];
            for ($i = 0; $i < $points; $i++) {
                $bucketDate = $from->copy()->addDays($i)->toDateString();
                $buckets[$bucketDate] = [
                    'bucket' => $bucketDate,
                    'total' => 0,
                    'accepted_count' => 0,
                    'rejected_count' => 0,
                    'quarantined_count' => 0,
                    'blocked_rate_pct' => 0.0,
                ];
            }

            foreach ($logs as $row) {
                $bucket = $row->created_at?->toDateString();
                if ($bucket === null || !array_key_exists($bucket, $buckets)) {
                    continue;
                }

                $verdict = strtoupper((string) $row->verdict);
                $buckets[$bucket]['total']++;
                if ($verdict === 'ACCEPTED') {
                    $buckets[$bucket]['accepted_count']++;
                } elseif ($verdict === 'REJECTED') {
                    $buckets[$bucket]['rejected_count']++;
                } elseif ($verdict === 'QUARANTINED') {
                    $buckets[$bucket]['quarantined_count']++;
                }
            }

            foreach ($buckets as $key => $bucket) {
                $blocked = (int) $bucket['rejected_count'] + (int) $bucket['quarantined_count'];
                $total = max((int) $bucket['total'], 1);
                $buckets[$key]['blocked_rate_pct'] = round(($blocked / $total) * 100, 2);
            }
        } else {
            $from = $now->copy()->startOfHour()->subHours($points - 1);
            $logs = FileUploadLog::query()
                ->where('created_at', '>=', $from)
                ->get(['created_at', 'verdict']);

            $buckets = [];
            for ($i = 0; $i < $points; $i++) {
                $bucketHour = $from->copy()->addHours($i)->format('Y-m-d H:00');
                $buckets[$bucketHour] = [
                    'bucket' => $bucketHour,
                    'total' => 0,
                    'accepted_count' => 0,
                    'rejected_count' => 0,
                    'quarantined_count' => 0,
                    'blocked_rate_pct' => 0.0,
                ];
            }

            foreach ($logs as $row) {
                $bucket = $row->created_at?->copy()->startOfHour()->format('Y-m-d H:00');
                if ($bucket === null || !array_key_exists($bucket, $buckets)) {
                    continue;
                }

                $verdict = strtoupper((string) $row->verdict);
                $buckets[$bucket]['total']++;
                if ($verdict === 'ACCEPTED') {
                    $buckets[$bucket]['accepted_count']++;
                } elseif ($verdict === 'REJECTED') {
                    $buckets[$bucket]['rejected_count']++;
                } elseif ($verdict === 'QUARANTINED') {
                    $buckets[$bucket]['quarantined_count']++;
                }
            }

            foreach ($buckets as $key => $bucket) {
                $blocked = (int) $bucket['rejected_count'] + (int) $bucket['quarantined_count'];
                $total = max((int) $bucket['total'], 1);
                $buckets[$key]['blocked_rate_pct'] = round(($blocked / $total) * 100, 2);
            }
        }

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_DASHBOARD_UPLOAD_TREND',
            'message' => 'Upload trend loaded',
            'data' => [
                'granularity' => $granularity,
                'points' => $points,
                'generated_at' => now()->toISOString(),
                'buckets' => array_values($buckets),
            ],
        ]);
    }

    public function dashboardHousekeepingHistory(Request $request): JsonResponse
    {
        $hours = $this->normalizedHours($request->integer('hours', 24));
        $limit = max(1, min($request->integer('limit', 20), 100));
        $from = now()->subHours($hours);

        $runs = AuditLog::query()
            ->where('event_type', 'IDEMPOTENCY_PURGE_COMPLETED')
            ->where('occurred_at', '>=', $from)
            ->orderByDesc('occurred_at')
            ->limit($limit)
            ->get(['payload', 'occurred_at'])
            ->map(static function (AuditLog $log): array {
                $payload = is_array($log->payload) ? $log->payload : [];

                return [
                    'run_at' => $log->occurred_at?->toISOString(),
                    'deleted_records' => (int) ($payload['deleted_records'] ?? 0),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_DASHBOARD_HOUSEKEEPING_HISTORY',
            'message' => 'Housekeeping history loaded',
            'data' => [
                'window_hours' => $hours,
                'limit' => $limit,
                'count' => $runs->count(),
                'runs' => $runs,
            ],
        ]);
    }

    public function dashboardHousekeepingTrend(Request $request): JsonResponse
    {
        $days = max(1, min($request->integer('days', 7), 90));
        $from = now()->startOfDay()->subDays($days - 1);

        $logs = AuditLog::query()
            ->where('event_type', 'IDEMPOTENCY_PURGE_COMPLETED')
            ->where('occurred_at', '>=', $from)
            ->orderBy('occurred_at')
            ->get(['payload', 'occurred_at']);

        $buckets = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i)->toDateString();
            $buckets[$date] = [
                'date' => $date,
                'runs' => 0,
                'deleted_records' => 0,
            ];
        }

        foreach ($logs as $log) {
            $date = $log->occurred_at?->toDateString();
            if ($date === null || !isset($buckets[$date])) {
                continue;
            }

            $payload = is_array($log->payload) ? $log->payload : [];
            $buckets[$date]['runs']++;
            $buckets[$date]['deleted_records'] += (int) ($payload['deleted_records'] ?? 0);
        }

        $trend = array_values($buckets);

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_DASHBOARD_HOUSEKEEPING_TREND',
            'message' => 'Housekeeping trend loaded',
            'data' => [
                'days' => $days,
                'generated_at' => now()->toISOString(),
                'trend' => $trend,
            ],
        ]);
    }

    public function systemReadiness(): JsonResponse
    {
        $checks = [];

        try {
            DB::select('select 1');
            $checks[] = [
                'code' => 'DB_CONNECTION',
                'status' => 'PASS',
                'message' => 'Database connection is healthy',
                'meta' => [
                    'connection' => (string) config('database.default'),
                ],
            ];
        } catch (Throwable $exception) {
            $checks[] = [
                'code' => 'DB_CONNECTION',
                'status' => 'FAIL',
                'message' => 'Database connection failed',
                'meta' => [
                    'error' => $exception->getMessage(),
                ],
            ];
        }

        $queueConnection = (string) config('queue.default', 'sync');
        $checks[] = [
            'code' => 'QUEUE_CONNECTION',
            'status' => $queueConnection === 'sync' ? 'WARN' : 'PASS',
            'message' => $queueConnection === 'sync'
                ? 'Queue driver is sync; use redis/database for production'
                : 'Queue driver is non-sync',
            'meta' => [
                'driver' => $queueConnection,
            ],
        ];

        $providerCoverage = $this->providerCredentialCoverage();
        $checks[] = [
            'code' => 'PROVIDER_CREDENTIALS',
            'status' => $providerCoverage['ready_count'] > 0 ? 'PASS' : 'WARN',
            'message' => $providerCoverage['ready_count'] > 0
                ? 'At least one provider credential set is available'
                : 'No provider credential set found',
            'meta' => $providerCoverage,
        ];

        $paymentCoverage = $this->paymentGatewayCoverage();
        $checks[] = [
            'code' => 'PAYMENT_GATEWAY_WEBHOOK_SECRETS',
            'status' => $paymentCoverage['ready_count'] > 0 ? 'PASS' : 'WARN',
            'message' => $paymentCoverage['ready_count'] > 0
                ? 'At least one payment gateway webhook secret is configured'
                : 'No payment gateway webhook secret configured',
            'meta' => $paymentCoverage,
        ];

        $recentPurge = AuditLog::query()
            ->where('event_type', 'IDEMPOTENCY_PURGE_COMPLETED')
            ->where('occurred_at', '>=', now()->subHours(2))
            ->exists();

        $checks[] = [
            'code' => 'HOUSEKEEPING_RECENT_PERSISTENCE',
            'status' => $recentPurge ? 'PASS' : 'WARN',
            'message' => $recentPurge
                ? 'Recent idempotency purge run detected'
                : 'No purge run detected in last 2 hours',
            'meta' => [
                'window_hours' => 2,
            ],
        ];

        $failedCount = collect($checks)->where('status', 'FAIL')->count();
        $warnCount = collect($checks)->where('status', 'WARN')->count();
        $passCount = collect($checks)->where('status', 'PASS')->count();

        $ready = $failedCount === 0;
        $score = round(($passCount / max(1, count($checks))) * 100, 2);

        return response()->json([
            'success' => true,
            'code' => 'SYSTEM_READINESS_REPORT',
            'message' => 'System readiness report generated',
            'data' => [
                'ready' => $ready,
                'score' => $score,
                'summary' => [
                    'pass' => $passCount,
                    'warn' => $warnCount,
                    'fail' => $failedCount,
                ],
                'checks' => $checks,
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }

    public function dashboardMetricsExcel(Request $request): StreamedResponse
    {
        $hours = $this->normalizedHours($request->integer('hours', 24));
        $alertThreshold = $this->normalizedAlertThreshold(
            $request->input('alert_success_rate_threshold', config('services.dashboard.provider_success_rate_alert_threshold', 85))
        );
        $alertMinAttempts = $this->normalizedAlertMinAttempts(
            $request->input('alert_min_attempts', config('services.dashboard.provider_alert_min_attempts', 5))
        );
        $paymentAlertThreshold = $this->normalizedAlertThreshold(
            $request->input('payment_alert_paid_rate_threshold', config('services.dashboard.payment_paid_rate_alert_threshold', 75))
        );
        $paymentAlertMinTotal = $this->normalizedAlertMinAttempts(
            $request->input('payment_alert_min_total', config('services.dashboard.payment_alert_min_total', 5))
        );

        $metrics = $this->buildDashboardMetrics(
            $hours,
            $alertThreshold,
            $alertMinAttempts,
            $paymentAlertThreshold,
            $paymentAlertMinTotal
        );
        $xml = $this->buildSpreadsheetXml($metrics);
        $fileName = 'dashboard-metrics-'.now()->format('Ymd-His').'.xls';

        return response()->streamDownload(
            static function () use ($xml): void {
                echo $xml;
            },
            $fileName,
            [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildDashboardMetrics(
        int $hours,
        float $alertThreshold,
        int $alertMinAttempts,
        float $paymentAlertThreshold,
        int $paymentAlertMinTotal
    ): array
    {
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
            ->values()
            ->all();

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
            ->values()
            ->all();

        $providerAlerts = collect($providerRows)
            ->filter(static fn (array $row): bool => (int) $row['attempts'] >= $alertMinAttempts)
            ->filter(static fn (array $row): bool => (float) $row['success_rate_pct'] < $alertThreshold)
            ->map(static fn (array $row): array => [
                'provider_id' => (int) $row['provider_id'],
                'provider_code' => (string) $row['provider_code'],
                'provider_name' => (string) $row['provider_name'],
                'attempts' => (int) $row['attempts'],
                'success_rate_pct' => (float) $row['success_rate_pct'],
                'threshold_pct' => $alertThreshold,
                'severity' => ((float) $row['success_rate_pct'] < $alertThreshold * 0.75) ? 'HIGH' : 'MEDIUM',
            ])
            ->values()
            ->all();

        $paymentAlerts = collect($paymentRows)
            ->filter(static fn (array $row): bool => (int) $row['total'] >= $paymentAlertMinTotal)
            ->filter(static fn (array $row): bool => (float) $row['paid_rate_pct'] < $paymentAlertThreshold)
            ->map(static fn (array $row): array => [
                'gateway' => (string) $row['gateway'],
                'total' => (int) $row['total'],
                'paid_rate_pct' => (float) $row['paid_rate_pct'],
                'threshold_pct' => $paymentAlertThreshold,
                'severity' => ((float) $row['paid_rate_pct'] < $paymentAlertThreshold * 0.75) ? 'HIGH' : 'MEDIUM',
            ])
            ->values()
            ->all();

        $uploadAlertMinTotal = $this->normalizedAlertMinAttempts(
            config('services.dashboard.upload_alert_min_total', 5)
        );
        $uploadAlertBlockedRateThreshold = $this->normalizedAlertThreshold(
            config('services.dashboard.upload_blocked_rate_alert_threshold', 30)
        );

        $uploadLogs = FileUploadLog::query()
            ->where('created_at', '>=', $from)
            ->get(['verdict', 'upload_ip', 'reason', 'mime_type']);

        $uploadsByVerdict = $uploadLogs
            ->groupBy(static fn (FileUploadLog $row): string => strtoupper((string) $row->verdict))
            ->map(static fn (Collection $rows): int => $rows->count())
            ->all();

        $uploadTotal = $uploadLogs->count();
        $uploadRejected = (int) ($uploadsByVerdict['REJECTED'] ?? 0);
        $uploadQuarantined = (int) ($uploadsByVerdict['QUARANTINED'] ?? 0);
        $uploadBlocked = $uploadRejected + $uploadQuarantined;

        $uploadRows = [
            [
                'total' => $uploadTotal,
                'accepted_count' => (int) ($uploadsByVerdict['ACCEPTED'] ?? 0),
                'rejected_count' => $uploadRejected,
                'quarantined_count' => $uploadQuarantined,
                'blocked_rate_pct' => $this->percentage($uploadBlocked, max($uploadTotal, 1)),
            ],
        ];

        $uploadAlerts = $uploadLogs
            ->groupBy(static fn (FileUploadLog $row): string => (string) ($row->upload_ip ?? 'unknown'))
            ->map(function (Collection $rows, string $ip) use ($uploadAlertBlockedRateThreshold): array {
                $total = $rows->count();
                $rejected = $rows->filter(static fn (FileUploadLog $row): bool => strtoupper((string) $row->verdict) === 'REJECTED')->count();
                $quarantined = $rows->filter(static fn (FileUploadLog $row): bool => strtoupper((string) $row->verdict) === 'QUARANTINED')->count();
                $blocked = $rejected + $quarantined;
                $blockedRate = $this->percentage($blocked, max($total, 1));

                $topReasons = $rows
                    ->filter(static fn (FileUploadLog $row): bool => in_array(strtoupper((string) $row->verdict), ['REJECTED', 'QUARANTINED'], true))
                    ->map(static fn (FileUploadLog $row): string => (string) ($row->reason ?? 'UNKNOWN'))
                    ->countBy()
                    ->sortDesc()
                    ->take(3)
                    ->map(static fn (int $count, string $reason): array => ['reason' => $reason, 'count' => $count])
                    ->values()
                    ->all();

                return [
                    'ip' => $ip,
                    'total' => $total,
                    'rejected_count' => $rejected,
                    'quarantined_count' => $quarantined,
                    'blocked_rate_pct' => $blockedRate,
                    'threshold_pct' => $uploadAlertBlockedRateThreshold,
                    'severity' => $blockedRate >= ($uploadAlertBlockedRateThreshold * 1.5) ? 'HIGH' : 'MEDIUM',
                    'top_reasons' => $topReasons,
                ];
            })
            ->filter(static fn (array $row): bool => (int) $row['total'] >= $uploadAlertMinTotal)
            ->filter(static fn (array $row): bool => (float) $row['blocked_rate_pct'] >= $uploadAlertBlockedRateThreshold)
            ->sortByDesc('blocked_rate_pct')
            ->values()
            ->all();

        $purgeLogs = AuditLog::query()
            ->where('event_type', 'IDEMPOTENCY_PURGE_COMPLETED')
            ->where('occurred_at', '>=', $from)
            ->orderByDesc('occurred_at')
            ->get(['payload', 'occurred_at']);

        $idempotencyPurgeTotalDeleted = $purgeLogs->sum(static function (AuditLog $log): int {
            $payload = is_array($log->payload) ? $log->payload : [];

            return (int) ($payload['deleted_records'] ?? 0);
        });

        $lastPurge = $purgeLogs->first();

        return [
            'window_hours' => $hours,
            'generated_at' => now()->toISOString(),
            'housekeeping' => [
                'idempotency_purge' => [
                    'runs' => $purgeLogs->count(),
                    'total_deleted' => $idempotencyPurgeTotalDeleted,
                    'last_run_at' => $lastPurge?->occurred_at?->toISOString(),
                    'last_deleted' => (int) ((is_array($lastPurge?->payload) ? $lastPurge?->payload['deleted_records'] ?? 0 : 0)),
                ],
            ],
            'alerts' => [
                'config' => [
                    'provider_success_rate_threshold_pct' => $alertThreshold,
                    'provider_alert_min_attempts' => $alertMinAttempts,
                    'payment_paid_rate_threshold_pct' => $paymentAlertThreshold,
                    'payment_alert_min_total' => $paymentAlertMinTotal,
                    'upload_blocked_rate_threshold_pct' => $uploadAlertBlockedRateThreshold,
                    'upload_alert_min_total' => $uploadAlertMinTotal,
                ],
                'summary' => [
                    'provider_alert_count' => count($providerAlerts),
                    'payment_alert_count' => count($paymentAlerts),
                    'upload_alert_count' => count($uploadAlerts),
                    'has_alerts' => count($providerAlerts) > 0 || count($paymentAlerts) > 0 || count($uploadAlerts) > 0,
                ],
                'providers' => $providerAlerts,
                'payments' => $paymentAlerts,
                'uploads' => $uploadAlerts,
            ],
            'providers' => $providerRows,
            'payments' => $paymentRows,
            'uploads' => $uploadRows,
        ];
    }

    /**
     * @param array<string, mixed> $metrics
     */
    private function buildSpreadsheetXml(array $metrics): string
    {
        $providers = is_array($metrics['providers'] ?? null) ? $metrics['providers'] : [];
        $payments = is_array($metrics['payments'] ?? null) ? $metrics['payments'] : [];
        $uploads = is_array($metrics['uploads'] ?? null) ? $metrics['uploads'] : [];
        $alertContainer = is_array($metrics['alerts'] ?? null) ? $metrics['alerts'] : [];
        $providerAlerts = is_array($alertContainer['providers'] ?? null) ? $alertContainer['providers'] : [];
        $paymentAlerts = is_array($alertContainer['payments'] ?? null) ? $alertContainer['payments'] : [];
        $uploadAlerts = is_array($alertContainer['uploads'] ?? null) ? $alertContainer['uploads'] : [];

        $providerRows = array_map(static function (array $row): array {
            $failReasons = collect(is_array($row['top_fail_reasons'] ?? null) ? $row['top_fail_reasons'] : [])
                ->map(static fn (array $reason): string => ((string) ($reason['reason'] ?? 'unknown')).' ('.((int) ($reason['count'] ?? 0)).')')
                ->implode('; ');

            return [
                (string) ($row['provider_code'] ?? ''),
                (string) ($row['provider_name'] ?? ''),
                (string) ($row['attempts'] ?? 0),
                (string) ($row['success_count'] ?? 0),
                (string) ($row['failed_count'] ?? 0),
                (string) ($row['skipped_count'] ?? 0),
                (string) ($row['success_rate_pct'] ?? 0),
                (string) ($row['p95_latency_ms'] ?? ''),
                $failReasons,
            ];
        }, $providers);

        $paymentRows = array_map(static fn (array $row): array => [
            (string) ($row['gateway'] ?? ''),
            (string) ($row['total'] ?? 0),
            (string) ($row['paid_count'] ?? 0),
            (string) ($row['failed_count'] ?? 0),
            (string) ($row['unpaid_count'] ?? 0),
            (string) ($row['paid_rate_pct'] ?? 0),
        ], $payments);

        $uploadRows = array_map(static fn (array $row): array => [
            (string) ($row['total'] ?? 0),
            (string) ($row['accepted_count'] ?? 0),
            (string) ($row['rejected_count'] ?? 0),
            (string) ($row['quarantined_count'] ?? 0),
            (string) ($row['blocked_rate_pct'] ?? 0),
        ], $uploads);

        $providerAlertRows = array_map(static fn (array $row): array => [
            (string) ($row['provider_code'] ?? ''),
            (string) ($row['provider_name'] ?? ''),
            (string) ($row['attempts'] ?? 0),
            (string) ($row['success_rate_pct'] ?? 0),
            (string) ($row['threshold_pct'] ?? 0),
            (string) ($row['severity'] ?? ''),
        ], $providerAlerts);

        $paymentAlertRows = array_map(static fn (array $row): array => [
            (string) ($row['gateway'] ?? ''),
            (string) ($row['total'] ?? 0),
            (string) ($row['paid_rate_pct'] ?? 0),
            (string) ($row['threshold_pct'] ?? 0),
            (string) ($row['severity'] ?? ''),
        ], $paymentAlerts);

        $uploadAlertRows = array_map(static function (array $row): array {
            $reasons = collect(is_array($row['top_reasons'] ?? null) ? $row['top_reasons'] : [])
                ->map(static fn (array $reason): string => ((string) ($reason['reason'] ?? 'UNKNOWN')).' ('.((int) ($reason['count'] ?? 0)).')')
                ->implode('; ');

            return [
                (string) ($row['ip'] ?? 'unknown'),
                (string) ($row['total'] ?? 0),
                (string) ($row['rejected_count'] ?? 0),
                (string) ($row['quarantined_count'] ?? 0),
                (string) ($row['blocked_rate_pct'] ?? 0),
                (string) ($row['threshold_pct'] ?? 0),
                (string) ($row['severity'] ?? ''),
                $reasons,
            ];
        }, $uploadAlerts);

        $sheetMetaRows = [
            ['window_hours', (string) ($metrics['window_hours'] ?? 24)],
            ['generated_at', (string) ($metrics['generated_at'] ?? now()->toISOString())],
        ];

        $housekeeping = is_array($metrics['housekeeping'] ?? null) ? $metrics['housekeeping'] : [];
        $idempotencyPurge = is_array($housekeeping['idempotency_purge'] ?? null) ? $housekeeping['idempotency_purge'] : [];

        $housekeepingRows = [
            ['idempotency_purge_runs', (string) ($idempotencyPurge['runs'] ?? 0)],
            ['idempotency_purge_total_deleted', (string) ($idempotencyPurge['total_deleted'] ?? 0)],
            ['idempotency_purge_last_run_at', (string) ($idempotencyPurge['last_run_at'] ?? '')],
            ['idempotency_purge_last_deleted', (string) ($idempotencyPurge['last_deleted'] ?? 0)],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        $xml .= $this->buildWorksheetXml('Summary', ['key', 'value'], $sheetMetaRows);
        $xml .= $this->buildWorksheetXml('Housekeeping', ['key', 'value'], $housekeepingRows);
        $xml .= $this->buildWorksheetXml('Provider Metrics', [
            'provider_code', 'provider_name', 'attempts', 'success_count', 'failed_count', 'skipped_count', 'success_rate_pct', 'p95_latency_ms', 'top_fail_reasons',
        ], $providerRows);
        $xml .= $this->buildWorksheetXml('Payment Metrics', [
            'gateway', 'total', 'paid_count', 'failed_count', 'unpaid_count', 'paid_rate_pct',
        ], $paymentRows);
        $xml .= $this->buildWorksheetXml('Upload Metrics', [
            'total', 'accepted_count', 'rejected_count', 'quarantined_count', 'blocked_rate_pct',
        ], $uploadRows);
        $xml .= $this->buildWorksheetXml('Provider Alerts', [
            'provider_code', 'provider_name', 'attempts', 'success_rate_pct', 'threshold_pct', 'severity',
        ], $providerAlertRows);
        $xml .= $this->buildWorksheetXml('Payment Alerts', [
            'gateway', 'total', 'paid_rate_pct', 'threshold_pct', 'severity',
        ], $paymentAlertRows);
        $xml .= $this->buildWorksheetXml('Upload Alerts', [
            'ip', 'total', 'rejected_count', 'quarantined_count', 'blocked_rate_pct', 'threshold_pct', 'severity', 'top_reasons',
        ], $uploadAlertRows);
        $xml .= '</Workbook>';

        return $xml;
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, array<int, string>> $rows
     */
    private function buildWorksheetXml(string $sheetName, array $headers, array $rows): string
    {
        $xml = '<Worksheet ss:Name="'.$this->xmlValue($sheetName).'">';
        $xml .= '<Table>';
        $xml .= '<Row>';
        foreach ($headers as $header) {
            $xml .= '<Cell><Data ss:Type="String">'.$this->xmlValue($header).'</Data></Cell>';
        }
        $xml .= '</Row>';

        foreach ($rows as $row) {
            $xml .= '<Row>';
            foreach ($row as $cell) {
                $xml .= '<Cell><Data ss:Type="String">'.$this->xmlValue($cell).'</Data></Cell>';
            }
            $xml .= '</Row>';
        }

        $xml .= '</Table>';
        $xml .= '</Worksheet>';

        return $xml;
    }

    private function xmlValue(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function normalizedHours(int $hours): int
    {
        return max(1, min($hours, 168));
    }

    private function normalizedAlertThreshold(mixed $threshold): float
    {
        return (float) max(1, min((float) $threshold, 100));
    }

    private function normalizedAlertMinAttempts(mixed $minAttempts): int
    {
        return (int) max(1, min((int) $minAttempts, 500));
    }

    /**
     * @return array<string, mixed>
     */
    private function providerCredentialCoverage(): array
    {
        $providers = [
            'DIGIFLAZZ' => [
                (string) config('services.digiflazz.base_url'),
                (string) config('services.digiflazz.username'),
                (string) config('services.digiflazz.api_key'),
            ],
            'RAJABILLER' => [
                (string) config('services.rajabiller.base_url'),
                (string) config('services.rajabiller.username'),
                (string) config('services.rajabiller.api_key'),
            ],
            'ORDERKUOTA' => [
                (string) config('services.orderkuota.base_url'),
                (string) config('services.orderkuota.username'),
                (string) config('services.orderkuota.api_key'),
            ],
        ];

        $readyProviders = [];
        foreach ($providers as $code => $values) {
            $allFilled = collect($values)->every(static fn (string $value): bool => trim($value) !== '');
            if ($allFilled) {
                $readyProviders[] = $code;
            }
        }

        return [
            'total' => count($providers),
            'ready_count' => count($readyProviders),
            'ready_providers' => $readyProviders,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentGatewayCoverage(): array
    {
        $gateways = ['KLIKQRISS', 'MIDTRANS', 'DUITKU'];
        $ready = [];

        foreach ($gateways as $gateway) {
            $secret = (string) config('services.payment_gateways.'.$gateway.'.webhook_secret', '');
            if (trim($secret) !== '') {
                $ready[] = $gateway;
            }
        }

        return [
            'total' => count($gateways),
            'ready_count' => count($ready),
            'ready_gateways' => $ready,
        ];
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
