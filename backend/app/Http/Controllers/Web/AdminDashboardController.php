<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderProviderAttempt;
use App\Models\Payment;
use App\Models\Provider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $from = now()->subHours(24);

        $overview = [
            'orders_pending' => Order::query()->where('status', 'PENDING')->count(),
            'orders_processing' => Order::query()->where('status', 'PROCESSING')->count(),
            'orders_failed' => Order::query()->where('status', 'FAILED')->count(),
            'payments_unpaid' => Payment::query()->where('status', 'UNPAID')->count(),
            'providers_active' => Provider::query()->where('is_active', true)->count(),
            'providers_total' => Provider::query()->count(),
        ];

        $providerPerformance = OrderProviderAttempt::query()
            ->with('provider:id,code,name')
            ->where('attempted_at', '>=', $from)
            ->get()
            ->groupBy('provider_id')
            ->map(static function (Collection $attempts): array {
                $provider = $attempts->first()?->provider;
                $total = $attempts->count();
                $success = $attempts->whereIn('status', ['SUCCESS', 'PAID'])->count();
                $failed = $attempts->whereIn('status', ['FAILED', 'ERROR'])->count();

                return [
                    'provider_code' => (string) ($provider?->code ?? 'UNKNOWN'),
                    'provider_name' => (string) ($provider?->name ?? 'Unknown Provider'),
                    'attempts' => $total,
                    'success' => $success,
                    'failed' => $failed,
                    'success_rate' => round(($success / max(1, $total)) * 100, 2),
                ];
            })
            ->sortByDesc('attempts')
            ->values();

        $paymentPerformance = Payment::query()
            ->where('created_at', '>=', $from)
            ->get(['gateway', 'status'])
            ->groupBy(static fn (Payment $payment): string => strtoupper((string) $payment->gateway))
            ->map(static function (Collection $rows, string $gateway): array {
                $total = $rows->count();
                $paid = $rows->where('status', 'PAID')->count();

                return [
                    'gateway' => $gateway,
                    'total' => $total,
                    'paid' => $paid,
                    'unpaid' => $rows->where('status', 'UNPAID')->count(),
                    'failed' => $rows->whereIn('status', ['FAILED', 'EXPIRED'])->count(),
                    'paid_rate' => round(($paid / max(1, $total)) * 100, 2),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $housekeepingLogs = AuditLog::query()
            ->where('event_type', 'IDEMPOTENCY_PURGE_COMPLETED')
            ->orderByDesc('occurred_at')
            ->limit(5)
            ->get(['payload', 'occurred_at']);

        return view('admin.dashboard', [
            'overview' => $overview,
            'providerPerformance' => $providerPerformance,
            'paymentPerformance' => $paymentPerformance,
            'readiness' => $this->readinessChecks(),
            'housekeepingLogs' => $housekeepingLogs,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function readinessChecks(): array
    {
        $checks = [];

        try {
            DB::select('select 1');
            $checks[] = [
                'code' => 'DB_CONNECTION',
                'status' => 'PASS',
                'message' => 'Database connection is healthy',
            ];
        } catch (\Throwable $exception) {
            $checks[] = [
                'code' => 'DB_CONNECTION',
                'status' => 'FAIL',
                'message' => 'Database connection failed: '.$exception->getMessage(),
            ];
        }

        $queueDriver = (string) config('queue.default', 'sync');
        $checks[] = [
            'code' => 'QUEUE_CONNECTION',
            'status' => $queueDriver === 'sync' ? 'WARN' : 'PASS',
            'message' => $queueDriver === 'sync' ? 'Queue masih sync (kurang cocok untuk produksi).' : 'Queue non-sync terkonfigurasi.',
        ];

        $providersReady = collect([
            'digiflazz' => [
                config('services.digiflazz.base_url'),
                config('services.digiflazz.username'),
                config('services.digiflazz.api_key'),
            ],
            'rajabiller' => [
                config('services.rajabiller.base_url'),
                config('services.rajabiller.username'),
                config('services.rajabiller.api_key'),
            ],
            'orderkuota' => [
                config('services.orderkuota.base_url'),
                config('services.orderkuota.username'),
                config('services.orderkuota.api_key'),
            ],
        ])->filter(static fn (array $keys): bool => collect($keys)->every(static fn ($value): bool => is_string($value) && $value !== ''))
            ->count();

        $checks[] = [
            'code' => 'PROVIDER_CREDENTIALS',
            'status' => $providersReady > 0 ? 'PASS' : 'WARN',
            'message' => $providersReady > 0 ? 'Credential provider minimal satu tersedia.' : 'Belum ada credential provider yang lengkap.',
        ];

        $gatewaysReady = collect([
            config('services.midtrans.webhook_secret'),
            config('services.tripay.webhook_secret'),
            config('services.xendit.webhook_secret'),
        ])->filter(static fn ($value): bool => is_string($value) && $value !== '')->count();

        $checks[] = [
            'code' => 'PAYMENT_WEBHOOK_SECRET',
            'status' => $gatewaysReady > 0 ? 'PASS' : 'WARN',
            'message' => $gatewaysReady > 0 ? 'Webhook secret payment terdeteksi.' : 'Webhook secret payment belum diisi.',
        ];

        $summary = [
            'pass' => collect($checks)->where('status', 'PASS')->count(),
            'warn' => collect($checks)->where('status', 'WARN')->count(),
            'fail' => collect($checks)->where('status', 'FAIL')->count(),
        ];

        return [
            'summary' => $summary,
            'checks' => $checks,
            'score' => round(($summary['pass'] / max(1, count($checks))) * 100, 2),
        ];
    }
}
