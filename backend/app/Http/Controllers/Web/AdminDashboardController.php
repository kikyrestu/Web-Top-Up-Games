<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\V1\Admin\SystemOpsController;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
        ]);
    }
}
