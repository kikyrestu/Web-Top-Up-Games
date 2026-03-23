<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Audit\Services\AuditLogService;
use App\Domain\Catalog\Services\ProductSyncService;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;

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
}
