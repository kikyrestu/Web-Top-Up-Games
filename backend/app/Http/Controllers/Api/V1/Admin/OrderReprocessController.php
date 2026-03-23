<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Domain\Audit\Services\AuditLogService;
use App\Http\Controllers\Controller;
use App\Jobs\FulfillPaidOrderJob;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderReprocessController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function store(Request $request, string $orderCode): JsonResponse
    {
        $order = Order::query()->where('order_code', $orderCode)->first();

        if ($order === null) {
            return response()->json([
                'success' => false,
                'code' => 'ORDER_NOT_FOUND',
                'message' => 'Order not found',
                'data' => null,
            ], 404);
        }

        if ($order->status !== 'FAILED') {
            return response()->json([
                'success' => false,
                'code' => 'ORDER_NOT_REPROCESSABLE',
                'message' => 'Only FAILED order can be reprocessed manually',
                'data' => [
                    'current_status' => $order->status,
                ],
            ], 422);
        }

        $order->update([
            'status' => 'PAID',
            'processed_at' => null,
            'completed_at' => null,
        ]);

        FulfillPaidOrderJob::dispatch($order->id);

        $user = $request->user();

        $this->auditLogService->write([
            'event_type' => 'ORDER_REPROCESS_REQUESTED',
            'actor_type' => 'USER',
            'actor_id' => $user?->id,
            'entity_type' => 'ORDER',
            'entity_id' => $order->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'order_code' => $order->order_code,
                'previous_status' => 'FAILED',
                'new_status' => 'PAID',
            ],
            'occurred_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'code' => 'ORDER_REPROCESS_QUEUED',
            'message' => 'Reprocess job has been queued',
            'data' => [
                'order_code' => $order->order_code,
                'status' => $order->status,
            ],
        ]);
    }
}
