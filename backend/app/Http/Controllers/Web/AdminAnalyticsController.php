<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class AdminAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $windowDays = $this->resolveWindowDays((int) $request->integer('days', 30));
        $start = CarbonImmutable::now()->subDays($windowDays - 1)->startOfDay();
        $end = CarbonImmutable::now()->endOfDay();

        $orderBase = Order::query()->whereBetween('created_at', [$start, $end]);

        $totalOrders = (clone $orderBase)->count();
        $paymentConfirmedOrders = (clone $orderBase)
            ->whereIn('status', ['PAID', 'PROCESSING', 'SUCCESS', 'FAILED', 'DISPUTED', 'REFUNDED'])
            ->count();
        $successfulOrders = (clone $orderBase)->where('status', 'SUCCESS')->count();
        $failedOrders = (clone $orderBase)->where('status', 'FAILED')->count();

        $grossRevenue = (float) ((clone $orderBase)
            ->whereIn('status', ['PAID', 'PROCESSING', 'SUCCESS', 'FAILED', 'DISPUTED'])
            ->sum('final_amount'));
        $netRevenue = (float) ((clone $orderBase)
            ->where('status', 'SUCCESS')
            ->sum('final_amount'));
        $refundedAmount = (float) ((clone $orderBase)
            ->where('status', 'REFUNDED')
            ->sum('final_amount'));

        $funnel = [
            'checkout_initiated' => $totalOrders,
            'payment_confirmed' => $paymentConfirmedOrders,
            'fulfillment_success' => $successfulOrders,
            'payment_conversion_pct' => $totalOrders > 0 ? round(($paymentConfirmedOrders / $totalOrders) * 100, 2) : 0.0,
            'success_conversion_pct' => $totalOrders > 0 ? round(($successfulOrders / $totalOrders) * 100, 2) : 0.0,
        ];

        return view('admin.analytics.index', [
            'windowDays' => $windowDays,
            'timeSeries' => $this->timeSeries($start, $end),
            'funnel' => $funnel,
            'overview' => [
                'total_orders' => $totalOrders,
                'successful_orders' => $successfulOrders,
                'failed_orders' => $failedOrders,
                'gross_revenue' => round($grossRevenue, 2),
                'net_revenue' => round($netRevenue, 2),
                'refunded_amount' => round($refundedAmount, 2),
            ],
            'gatewayPerformance' => $this->gatewayPerformance($start, $end),
            'productPerformance' => $this->productPerformance($start, $end),
            'cohortRows' => $this->cohortRows($start, $end),
        ]);
    }

    private function resolveWindowDays(int $days): int
    {
        return in_array($days, [7, 14, 30, 60, 90], true) ? $days : 30;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function timeSeries(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $rows = Order::query()
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('COUNT(*) as orders_total')
            ->selectRaw("SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as orders_success")
            ->selectRaw("SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as orders_failed")
            ->selectRaw("SUM(CASE WHEN status IN ('PAID','PROCESSING','SUCCESS','FAILED','DISPUTED') THEN final_amount ELSE 0 END) as gmv")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return $rows
            ->map(static function ($row): array {
                $ordersTotal = (int) ($row->orders_total ?? 0);
                $ordersSuccess = (int) ($row->orders_success ?? 0);

                return [
                    'day' => (string) ($row->day ?? ''),
                    'orders_total' => $ordersTotal,
                    'orders_success' => $ordersSuccess,
                    'orders_failed' => (int) ($row->orders_failed ?? 0),
                    'gmv' => round((float) ($row->gmv ?? 0), 2),
                    'success_rate_pct' => $ordersTotal > 0 ? round(($ordersSuccess / $ordersTotal) * 100, 2) : 0.0,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function gatewayPerformance(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $rows = Payment::query()
            ->select('gateway')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END) as paid_count")
            ->selectRaw("SUM(CASE WHEN status IN ('FAILED','EXPIRED') THEN 1 ELSE 0 END) as failed_count")
            ->selectRaw("SUM(CASE WHEN status = 'UNPAID' THEN 1 ELSE 0 END) as unpaid_count")
            ->selectRaw("SUM(CASE WHEN status = 'REFUNDED' THEN 1 ELSE 0 END) as refunded_count")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('gateway')
            ->orderByDesc('total')
            ->get();

        return $rows
            ->map(static function ($row): array {
                $total = (int) ($row->total ?? 0);
                $paidCount = (int) ($row->paid_count ?? 0);
                $refundedCount = (int) ($row->refunded_count ?? 0);

                return [
                    'gateway' => (string) ($row->gateway ?? 'UNKNOWN'),
                    'total' => $total,
                    'paid_count' => $paidCount,
                    'failed_count' => (int) ($row->failed_count ?? 0),
                    'unpaid_count' => (int) ($row->unpaid_count ?? 0),
                    'refunded_count' => $refundedCount,
                    'paid_rate_pct' => $total > 0 ? round((($paidCount + $refundedCount) / $total) * 100, 2) : 0.0,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function productPerformance(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $rows = Order::query()
            ->join('products', 'products.id', '=', 'orders.product_id')
            ->select('products.id as product_id', 'products.name as product_name', 'products.type as product_type')
            ->selectRaw('COUNT(*) as orders_total')
            ->selectRaw("SUM(CASE WHEN orders.status = 'SUCCESS' THEN 1 ELSE 0 END) as orders_success")
            ->selectRaw("SUM(CASE WHEN orders.status = 'FAILED' THEN 1 ELSE 0 END) as orders_failed")
            ->selectRaw("SUM(CASE WHEN orders.status = 'REFUNDED' THEN 1 ELSE 0 END) as orders_refunded")
            ->selectRaw("SUM(CASE WHEN orders.status IN ('PAID','PROCESSING','SUCCESS','FAILED','DISPUTED') THEN orders.final_amount ELSE 0 END) as gmv")
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy('products.id', 'products.name', 'products.type')
            ->orderByDesc('orders_total')
            ->limit(20)
            ->get();

        return $rows
            ->map(static function ($row): array {
                $ordersTotal = (int) ($row->orders_total ?? 0);
                $ordersSuccess = (int) ($row->orders_success ?? 0);

                return [
                    'product_id' => (int) ($row->product_id ?? 0),
                    'product_name' => (string) ($row->product_name ?? '-'),
                    'product_type' => (string) ($row->product_type ?? '-'),
                    'orders_total' => $ordersTotal,
                    'orders_success' => $ordersSuccess,
                    'orders_failed' => (int) ($row->orders_failed ?? 0),
                    'orders_refunded' => (int) ($row->orders_refunded ?? 0),
                    'gmv' => round((float) ($row->gmv ?? 0), 2),
                    'success_rate_pct' => $ordersTotal > 0 ? round(($ordersSuccess / $ordersTotal) * 100, 2) : 0.0,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cohortRows(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $firstSuccessByUser = Order::query()
            ->select('user_id')
            ->selectRaw('MIN(created_at) as first_success_at')
            ->whereNotNull('user_id')
            ->where('status', 'SUCCESS')
            ->groupBy('user_id')
            ->get()
            ->filter(static fn ($row): bool => $row->first_success_at !== null)
            ->map(static fn ($row): array => [
                'user_id' => (int) $row->user_id,
                'first_success_at' => CarbonImmutable::parse((string) $row->first_success_at),
            ])
            ->filter(static fn (array $row): bool => $row['first_success_at']->between($start, $end))
            ->values();

        if ($firstSuccessByUser->isEmpty()) {
            return [];
        }

        $cohorts = $firstSuccessByUser
            ->groupBy(static fn (array $row): string => $row['first_success_at']->startOfWeek()->format('Y-m-d'))
            ->map(function (Collection $rows, string $cohortWeek): array {
                $userIds = $rows->pluck('user_id')->all();
                $cohortSize = count($userIds);

                $repeaters = 0;
                foreach ($rows as $row) {
                    $repeatExists = Order::query()
                        ->where('user_id', $row['user_id'])
                        ->where('status', 'SUCCESS')
                        ->where('created_at', '>', $row['first_success_at'])
                        ->where('created_at', '<=', $row['first_success_at']->addDays(30))
                        ->exists();

                    if ($repeatExists) {
                        $repeaters++;
                    }
                }

                return [
                    'cohort_week' => $cohortWeek,
                    'new_customers' => $cohortSize,
                    'repeat_30d' => $repeaters,
                    'retention_30d_pct' => $cohortSize > 0 ? round(($repeaters / $cohortSize) * 100, 2) : 0.0,
                ];
            })
            ->sortKeysDesc()
            ->values()
            ->all();

        return $cohorts;
    }
}
