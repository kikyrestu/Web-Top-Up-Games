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
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $metrics = $this->buildDashboardMetrics($hours, $alertThreshold, $alertMinAttempts);

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_DASHBOARD_METRICS',
            'message' => 'Dashboard metrics loaded',
            'data' => $metrics,
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

        $metrics = $this->buildDashboardMetrics($hours, $alertThreshold, $alertMinAttempts);
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
    private function buildDashboardMetrics(int $hours, float $alertThreshold, int $alertMinAttempts): array
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

        return [
            'window_hours' => $hours,
            'generated_at' => now()->toISOString(),
            'alerts' => [
                'config' => [
                    'provider_success_rate_threshold_pct' => $alertThreshold,
                    'provider_alert_min_attempts' => $alertMinAttempts,
                ],
                'providers' => $providerAlerts,
            ],
            'providers' => $providerRows,
            'payments' => $paymentRows,
        ];
    }

    /**
     * @param array<string, mixed> $metrics
     */
    private function buildSpreadsheetXml(array $metrics): string
    {
        $providers = is_array($metrics['providers'] ?? null) ? $metrics['providers'] : [];
        $payments = is_array($metrics['payments'] ?? null) ? $metrics['payments'] : [];
        $alertContainer = is_array($metrics['alerts'] ?? null) ? $metrics['alerts'] : [];
        $alerts = is_array($alertContainer['providers'] ?? null) ? $alertContainer['providers'] : [];

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

        $alertRows = array_map(static fn (array $row): array => [
            (string) ($row['provider_code'] ?? ''),
            (string) ($row['provider_name'] ?? ''),
            (string) ($row['attempts'] ?? 0),
            (string) ($row['success_rate_pct'] ?? 0),
            (string) ($row['threshold_pct'] ?? 0),
            (string) ($row['severity'] ?? ''),
        ], $alerts);

        $sheetMetaRows = [
            ['window_hours', (string) ($metrics['window_hours'] ?? 24)],
            ['generated_at', (string) ($metrics['generated_at'] ?? now()->toISOString())],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        $xml .= $this->buildWorksheetXml('Summary', ['key', 'value'], $sheetMetaRows);
        $xml .= $this->buildWorksheetXml('Provider Metrics', [
            'provider_code', 'provider_name', 'attempts', 'success_count', 'failed_count', 'skipped_count', 'success_rate_pct', 'p95_latency_ms', 'top_fail_reasons',
        ], $providerRows);
        $xml .= $this->buildWorksheetXml('Payment Metrics', [
            'gateway', 'total', 'paid_count', 'failed_count', 'unpaid_count', 'paid_rate_pct',
        ], $paymentRows);
        $xml .= $this->buildWorksheetXml('Provider Alerts', [
            'provider_code', 'provider_name', 'attempts', 'success_rate_pct', 'threshold_pct', 'severity',
        ], $alertRows);
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
