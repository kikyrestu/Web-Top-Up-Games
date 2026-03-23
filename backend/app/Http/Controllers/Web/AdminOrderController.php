<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Audit\Services\AuditLogService;
use App\Http\Controllers\Controller;
use App\Jobs\FulfillPaidOrderJob;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminOrderController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $status = strtoupper((string) $request->query('status', ''));
        $search = trim((string) $request->query('q', ''));

        $orders = Order::query()
            ->with([
                'product:id,name,type',
                'payment:id,order_id,gateway,gateway_reference,status,amount',
            ])
            ->when($status !== '', static fn ($query) => $query->where('status', $status))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('order_code', 'like', '%'.$search.'%')
                        ->orWhere('customer_target', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
        ]);
    }

    public function show(string $orderCode): View
    {
        $order = Order::query()
            ->with([
                'product:id,name,type',
                'items:id,order_id,quantity,unit_price,subtotal',
                'payment:id,order_id,gateway,gateway_reference,status,amount,method,paid_at,expired_at,meta',
                'providerAttempts:id,order_id,provider_id,attempt_no,status,provider_ref,request_payload,response_payload,attempted_at',
                'providerAttempts.provider:id,code,name',
            ])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function reprocess(Request $request, string $orderCode): RedirectResponse
    {
        $order = Order::query()->where('order_code', $orderCode)->first();

        if ($order === null) {
            return redirect()->route('admin.orders.index')->withErrors([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        if ($order->status !== 'FAILED') {
            return redirect()->route('admin.orders.show', ['orderCode' => $orderCode])->withErrors([
                'order' => 'Hanya order FAILED yang bisa direprocess.',
            ]);
        }

        $order->update([
            'status' => 'PAID',
            'processed_at' => null,
            'completed_at' => null,
        ]);

        FulfillPaidOrderJob::dispatch((int) $order->id);

        $this->auditLogService->write([
            'event_type' => 'ORDER_REPROCESS_REQUESTED_WEB',
            'actor_type' => 'USER',
            'actor_id' => auth()->id(),
            'entity_type' => 'ORDER',
            'entity_id' => $order->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'order_code' => $order->order_code,
                'previous_status' => 'FAILED',
                'new_status' => 'PAID',
                'channel' => 'web_admin',
            ],
            'occurred_at' => now(),
        ]);

        return redirect()
            ->route('admin.orders.show', ['orderCode' => $orderCode])
            ->with('notice', 'Reprocess job berhasil dimasukkan ke queue.');
    }
}
