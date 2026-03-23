<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\V1\Admin\SystemOpsController;
use App\Http\Controllers\Controller;
use App\Http\Middleware\GlobalRateLimit;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminDashboardController extends Controller
{
    public function __construct(private readonly SystemOpsController $systemOpsController)
    {
    }

    public function index(Request $request): View
    {
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
            'rateLimitStats' => $this->rateLimitStats(),
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

    /**
     * @return array<string, mixed>
     */
    private function rateLimitStats(): array
    {
        $profiles = GlobalRateLimit::monitoredProfiles();
        $hours = collect(range(0, 11))
            ->map(static fn (int $offset) => now()->subHours(11 - $offset))
            ->values();

        $rows = [];
        $totals = [];

        foreach ($profiles as $profile) {
            $profileHits = 0;
            $profileBlocked = 0;

            foreach ($hours as $hour) {
                $hourKey = $hour->format('YmdH');
                $hits = (int) Cache::get('rate_limit_metric:'.$profile.':hits:'.$hourKey, 0);
                $blocked = (int) Cache::get('rate_limit_metric:'.$profile.':blocked:'.$hourKey, 0);
                $rate = $hits > 0 ? round(($blocked / $hits) * 100, 2) : 0.0;

                $rows[] = [
                    'profile' => $profile,
                    'hour' => $hour->format('Y-m-d H:00'),
                    'hits' => $hits,
                    'blocked' => $blocked,
                    'blocked_rate_pct' => $rate,
                ];

                $profileHits += $hits;
                $profileBlocked += $blocked;
            }

            $totals[] = [
                'profile' => $profile,
                'hits' => $profileHits,
                'blocked' => $profileBlocked,
                'blocked_rate_pct' => $profileHits > 0 ? round(($profileBlocked / $profileHits) * 100, 2) : 0.0,
            ];
        }

        return [
            'rows' => $rows,
            'totals' => $totals,
            'window_label' => 'Last 12 hours',
        ];
    }
}
