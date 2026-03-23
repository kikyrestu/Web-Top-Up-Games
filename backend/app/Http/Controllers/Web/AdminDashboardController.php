<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\V1\Admin\SystemOpsController;
use App\Http\Controllers\Controller;
use App\Http\Middleware\GlobalRateLimit;
use App\Models\RateLimitMetric;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminDashboardController extends Controller
{
    public function __construct(private readonly SystemOpsController $systemOpsController)
    {
    }

    public function index(Request $request): View
    {
        $rateLimitWindowHours = $this->resolveRateLimitWindowHours((int) $request->integer('rl_hours', 12));

        $overviewResponse = $this->systemOpsController->dashboardOverview();
        $metricsResponse = $this->systemOpsController->dashboardMetrics();
        $alertsResponse = $this->systemOpsController->dashboardAlerts($request);
        $housekeepingResponse = $this->systemOpsController->dashboardHousekeeping($request);
        $housekeepingHistoryResponse = $this->systemOpsController->dashboardHousekeepingHistory($request);
        $readinessResponse = $this->systemOpsController->systemReadiness();

        /** @var array<string, mixed> $overviewPayload */
        $overviewPayload = $overviewResponse->getData(true);
        /** @var array<string, mixed> $metricsPayload */
        $metricsPayload = $metricsResponse->getData(true);
        /** @var array<string, mixed> $alertsPayload */
        $alertsPayload = $alertsResponse->getData(true);
        /** @var array<string, mixed> $housekeepingPayload */
        $housekeepingPayload = $housekeepingResponse->getData(true);
        /** @var array<string, mixed> $housekeepingHistoryPayload */
        $housekeepingHistoryPayload = $housekeepingHistoryResponse->getData(true);
        /** @var array<string, mixed> $readinessPayload */
        $readinessPayload = $readinessResponse->getData(true);

        $recentOrders = Order::query()
            ->with(['product:id,name,type', 'payment:id,order_id,status'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'overview' => is_array($overviewPayload['data'] ?? null) ? $overviewPayload['data'] : [],
            'metrics' => is_array($metricsPayload['data'] ?? null) ? $metricsPayload['data'] : [],
            'alerts' => is_array($alertsPayload['data'] ?? null) ? $alertsPayload['data'] : [],
            'housekeeping' => is_array($housekeepingPayload['data'] ?? null) ? $housekeepingPayload['data'] : [],
            'housekeepingHistory' => is_array($housekeepingHistoryPayload['data'] ?? null) ? $housekeepingHistoryPayload['data'] : [],
            'readiness' => is_array($readinessPayload['data'] ?? null) ? $readinessPayload['data'] : [],
            'recentOrders' => $recentOrders,
            'rateLimitStats' => $this->rateLimitStats($rateLimitWindowHours),
            'rateLimitWindowHours' => $rateLimitWindowHours,
        ]);
    }

    public function alerts(Request $request): View
    {
        $alertsResponse = $this->systemOpsController->dashboardAlerts($request);
        $metricsResponse = $this->systemOpsController->dashboardMetrics();
        $severity = strtoupper(trim((string) $request->query('severity', 'ALL')));

        /** @var array<string, mixed> $alertsPayload */
        $alertsPayload = $alertsResponse->getData(true);
        /** @var array<string, mixed> $metricsPayload */
        $metricsPayload = $metricsResponse->getData(true);

        $alertsData = is_array($alertsPayload['data'] ?? null) ? $alertsPayload['data'] : [];

        if (in_array($severity, ['HIGH', 'MEDIUM'], true)) {
            $providerAlerts = collect(is_iterable($alertsData['alerts']['providers'] ?? null) ? $alertsData['alerts']['providers'] : [])
                ->filter(static fn (array $row): bool => strtoupper((string) ($row['severity'] ?? '')) === $severity)
                ->values()
                ->all();

            $paymentAlerts = collect(is_iterable($alertsData['alerts']['payments'] ?? null) ? $alertsData['alerts']['payments'] : [])
                ->filter(static fn (array $row): bool => strtoupper((string) ($row['severity'] ?? '')) === $severity)
                ->values()
                ->all();

            $alertsData['alerts']['providers'] = $providerAlerts;
            $alertsData['alerts']['payments'] = $paymentAlerts;
        }

        return view('admin.alerts', [
            'alerts' => $alertsData,
            'metrics' => is_array($metricsPayload['data'] ?? null) ? $metricsPayload['data'] : [],
            'filters' => [
                'hours' => (int) $request->integer('hours', 24),
                'severity' => $severity,
                'alert_success_rate_threshold' => (float) $request->input('alert_success_rate_threshold', config('services.dashboard.provider_success_rate_alert_threshold', 85)),
                'alert_min_attempts' => (int) $request->input('alert_min_attempts', config('services.dashboard.provider_alert_min_attempts', 5)),
                'payment_alert_paid_rate_threshold' => (float) $request->input('payment_alert_paid_rate_threshold', config('services.dashboard.payment_paid_rate_alert_threshold', 75)),
                'payment_alert_min_total' => (int) $request->input('payment_alert_min_total', config('services.dashboard.payment_alert_min_total', 5)),
            ],
        ]);
    }

    public function metricsExcel(Request $request): StreamedResponse
    {
        return $this->systemOpsController->dashboardMetricsExcel($request);
    }

    public function rateLimitCsv(Request $request): StreamedResponse
    {
        $windowHours = $this->resolveRateLimitWindowHours((int) $request->integer('rl_hours', 12));
        $stats = $this->rateLimitStats($windowHours);
        $rows = collect(is_iterable($stats['rows'] ?? null) ? $stats['rows'] : []);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['profile', 'hour', 'hits', 'blocked', 'blocked_rate_pct', 'severity']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    (string) ($row['profile'] ?? ''),
                    (string) ($row['hour'] ?? ''),
                    (int) ($row['hits'] ?? 0),
                    (int) ($row['blocked'] ?? 0),
                    (float) ($row['blocked_rate_pct'] ?? 0),
                    (string) ($row['severity'] ?? 'LOW'),
                ]);
            }

            fclose($handle);
        }, 'rate-limit-monitor.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rateLimitStats(int $windowHours = 12): array
    {
        $profiles = GlobalRateLimit::monitoredProfiles();
        $windowHours = $this->resolveRateLimitWindowHours($windowHours);
        $hours = collect(range(0, $windowHours - 1))
            ->map(fn (int $offset) => now()->subHours(($windowHours - 1) - $offset))
            ->values();

        $dbMetrics = $this->loadRateLimitMetricsFromDatabase($profiles, $hours);

        $rows = [];
        $totals = [];

        foreach ($profiles as $profile) {
            $profileHits = 0;
            $profileBlocked = 0;

            foreach ($hours as $hour) {
                $hourKey = $hour->format('YmdH');
                $dbKey = $profile.'|'.$hour->copy()->startOfHour()->format('Y-m-d H:i:s');
                $dbValue = $dbMetrics->get($dbKey);

                $hits = (int) ($dbValue['hits'] ?? Cache::get('rate_limit_metric:'.$profile.':hits:'.$hourKey, 0));
                $blocked = (int) ($dbValue['blocked'] ?? Cache::get('rate_limit_metric:'.$profile.':blocked:'.$hourKey, 0));
                $rate = $hits > 0 ? round(($blocked / $hits) * 100, 2) : 0.0;
                $severity = $this->severityFromRate((float) $rate);

                $rows[] = [
                    'profile' => $profile,
                    'hour' => $hour->format('Y-m-d H:00'),
                    'hits' => $hits,
                    'blocked' => $blocked,
                    'blocked_rate_pct' => $rate,
                    'severity' => $severity,
                ];

                $profileHits += $hits;
                $profileBlocked += $blocked;
            }

            $totals[] = [
                'profile' => $profile,
                'hits' => $profileHits,
                'blocked' => $profileBlocked,
                'blocked_rate_pct' => $profileHits > 0 ? round(($profileBlocked / $profileHits) * 100, 2) : 0.0,
                'severity' => $this->severityFromRate($profileHits > 0 ? (($profileBlocked / $profileHits) * 100) : 0.0),
            ];
        }

        return [
            'rows' => $rows,
            'totals' => $totals,
            'window_label' => 'Last '.$windowHours.' hours',
            'window_hours' => $windowHours,
        ];
    }

    private function resolveRateLimitWindowHours(int $hours): int
    {
        return in_array($hours, [1, 6, 12, 24], true) ? $hours : 12;
    }

    private function severityFromRate(float $rate): string
    {
        if ($rate >= 30.0) {
            return 'HIGH';
        }

        if ($rate >= 10.0) {
            return 'MEDIUM';
        }

        return 'LOW';
    }

    /**
     * @param array<int, string> $profiles
     */
    private function loadRateLimitMetricsFromDatabase(array $profiles, \Illuminate\Support\Collection $hours): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('rate_limit_metrics')) {
            return collect();
        }

        $start = $hours->first();
        $end = $hours->last();

        if ($start === null || $end === null) {
            return collect();
        }

        try {
            return RateLimitMetric::query()
                ->whereIn('profile', $profiles)
                ->whereBetween('hour_bucket', [$start->copy()->startOfHour(), $end->copy()->endOfHour()])
                ->get()
                ->mapWithKeys(static fn (RateLimitMetric $row): array => [
                    $row->profile.'|'.$row->hour_bucket?->copy()->startOfHour()->format('Y-m-d H:i:s') => [
                        'hits' => (int) $row->hits,
                        'blocked' => (int) $row->blocked,
                    ],
                ]);
        } catch (QueryException) {
            return collect();
        }
    }
}
