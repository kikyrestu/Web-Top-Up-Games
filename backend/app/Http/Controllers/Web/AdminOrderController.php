<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Audit\Services\AuditLogService;
use App\Http\Controllers\Controller;
use App\Jobs\FulfillPaidOrderJob;
use App\Models\Order;
use App\Models\OrderOperationLog;
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

    public function show(Request $request, string $orderCode): View
    {
        $payloadSearch = trim((string) $request->query('payload_q', ''));

        $order = Order::query()
            ->with([
                'product:id,name,type',
                'items:id,order_id,quantity,unit_price,subtotal',
                'payment:id,order_id,gateway,gateway_reference,status,amount,method,paid_at,expired_at,meta',
                'providerAttempts:id,order_id,provider_id,attempt_no,status,provider_ref,request_payload,response_payload,attempted_at',
                'providerAttempts.provider:id,code,name',
                'operationLogs:id,order_id,actor_user_id,action_type,previous_status,new_status,refund_amount,note,meta,acted_at',
                'operationLogs.actor:id,name',
            ])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        $filteredAttempts = $order->providerAttempts
            ->sortBy('attempt_no')
            ->filter(function ($attempt) use ($payloadSearch): bool {
                if ($payloadSearch === '') {
                    return true;
                }

                $needle = mb_strtolower($payloadSearch);
                $requestJson = mb_strtolower(json_encode($attempt->request_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
                $responseJson = mb_strtolower(json_encode($attempt->response_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');

                return str_contains($requestJson, $needle)
                    || str_contains($responseJson, $needle)
                    || str_contains(mb_strtolower((string) $attempt->status), $needle)
                    || str_contains(mb_strtolower((string) $attempt->provider_ref), $needle);
            })
            ->values();

        return view('admin.orders.show', [
            'order' => $order,
            'attempts' => $filteredAttempts,
            'operationLogs' => $order->operationLogs->sortByDesc('acted_at')->values(),
            'payloadSearch' => $payloadSearch,
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

        $this->writeOperationLog(
            $order,
            $request,
            'REPROCESS',
            'FAILED',
            'PAID',
            null,
            'Manual reprocess dari panel admin.',
            [
                'channel' => 'web_admin',
            ],
        );

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

    public function voidOrder(Request $request, string $orderCode): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $order = Order::query()->with('payment')->where('order_code', $orderCode)->first();

        if ($order === null) {
            return redirect()->route('admin.orders.index')->withErrors([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        if (!in_array((string) $order->status, ['PENDING', 'PAID'], true)) {
            return redirect()->route('admin.orders.show', ['orderCode' => $orderCode])->withErrors([
                'order' => 'Order hanya bisa di-void saat status PENDING atau PAID.',
            ]);
        }

        $previousStatus = (string) $order->status;

        $order->update([
            'status' => 'VOIDED',
        ]);

        if ($order->payment !== null) {
            $order->payment->update([
                'status' => 'VOIDED',
            ]);
        }

        $note = trim((string) $validated['note']);

        $this->writeOperationLog(
            $order,
            $request,
            'VOID',
            $previousStatus,
            'VOIDED',
            null,
            $note,
            [
                'payment_status' => $order->payment?->status,
            ],
        );

        $this->auditLogService->write([
            'event_type' => 'ORDER_VOIDED_WEB',
            'actor_type' => 'USER',
            'actor_id' => auth()->id(),
            'entity_type' => 'ORDER',
            'entity_id' => $order->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'order_code' => $order->order_code,
                'previous_status' => $previousStatus,
                'new_status' => 'VOIDED',
                'note' => $note,
                'channel' => 'web_admin',
            ],
            'occurred_at' => now(),
        ]);

        return redirect()->route('admin.orders.show', ['orderCode' => $orderCode])->with('notice', 'Order berhasil di-void.');
    }

    public function refundOrder(Request $request, string $orderCode): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = Order::query()->with('payment')->where('order_code', $orderCode)->first();

        if ($order === null) {
            return redirect()->route('admin.orders.index')->withErrors([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        if (!in_array((string) $order->status, ['PAID', 'PROCESSING', 'SUCCESS', 'FAILED', 'DISPUTED'], true)) {
            return redirect()->route('admin.orders.show', ['orderCode' => $orderCode])->withErrors([
                'order' => 'Order hanya bisa di-refund saat status PAID, PROCESSING, SUCCESS, FAILED, atau DISPUTED.',
            ]);
        }

        $refundAmount = isset($validated['refund_amount']) ? (float) $validated['refund_amount'] : (float) $order->final_amount;

        if ($refundAmount > (float) $order->final_amount) {
            return redirect()->route('admin.orders.show', ['orderCode' => $orderCode])->withErrors([
                'refund_amount' => 'Nilai refund tidak boleh melebihi nominal order.',
            ])->withInput();
        }

        $previousStatus = (string) $order->status;

        $order->update([
            'status' => 'REFUNDED',
        ]);

        if ($order->payment !== null) {
            $order->payment->update([
                'status' => 'REFUNDED',
            ]);
        }

        $note = trim((string) $validated['note']);

        $this->writeOperationLog(
            $order,
            $request,
            'REFUND',
            $previousStatus,
            'REFUNDED',
            $refundAmount,
            $note,
            [
                'payment_status' => $order->payment?->status,
            ],
        );

        $this->auditLogService->write([
            'event_type' => 'ORDER_REFUNDED_WEB',
            'actor_type' => 'USER',
            'actor_id' => auth()->id(),
            'entity_type' => 'ORDER',
            'entity_id' => $order->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'order_code' => $order->order_code,
                'previous_status' => $previousStatus,
                'new_status' => 'REFUNDED',
                'refund_amount' => round($refundAmount, 2),
                'note' => $note,
                'channel' => 'web_admin',
            ],
            'occurred_at' => now(),
        ]);

        return redirect()->route('admin.orders.show', ['orderCode' => $orderCode])->with('notice', 'Order berhasil di-refund.');
    }

    public function disputeOrder(Request $request, string $orderCode): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $order = Order::query()->with('payment')->where('order_code', $orderCode)->first();

        if ($order === null) {
            return redirect()->route('admin.orders.index')->withErrors([
                'order' => 'Order tidak ditemukan.',
            ]);
        }

        if (!in_array((string) $order->status, ['PENDING', 'PAID', 'PROCESSING', 'SUCCESS', 'FAILED'], true)) {
            return redirect()->route('admin.orders.show', ['orderCode' => $orderCode])->withErrors([
                'order' => 'Order tidak dapat dipindahkan ke status DISPUTED dari status saat ini.',
            ]);
        }

        $previousStatus = (string) $order->status;

        $order->update([
            'status' => 'DISPUTED',
        ]);

        $note = trim((string) $validated['note']);

        $this->writeOperationLog(
            $order,
            $request,
            'DISPUTE',
            $previousStatus,
            'DISPUTED',
            null,
            $note,
            [
                'payment_status' => $order->payment?->status,
            ],
        );

        $this->auditLogService->write([
            'event_type' => 'ORDER_DISPUTED_WEB',
            'actor_type' => 'USER',
            'actor_id' => auth()->id(),
            'entity_type' => 'ORDER',
            'entity_id' => $order->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'order_code' => $order->order_code,
                'previous_status' => $previousStatus,
                'new_status' => 'DISPUTED',
                'note' => $note,
                'channel' => 'web_admin',
            ],
            'occurred_at' => now(),
        ]);

        return redirect()->route('admin.orders.show', ['orderCode' => $orderCode])->with('notice', 'Order berhasil ditandai dispute.');
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function writeOperationLog(
        Order $order,
        Request $request,
        string $actionType,
        ?string $previousStatus,
        ?string $newStatus,
        ?float $refundAmount,
        ?string $note,
        array $meta = [],
    ): void {
        OrderOperationLog::query()->create([
            'order_id' => $order->id,
            'actor_user_id' => auth()->id(),
            'action_type' => strtoupper($actionType),
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'refund_amount' => $refundAmount !== null ? round($refundAmount, 2) : null,
            'note' => $note,
            'meta' => array_merge([
                'order_code' => $order->order_code,
                'request_id' => $request->header('x-request-id'),
                'ip_address' => $request->ip(),
            ], $meta),
            'acted_at' => now(),
        ]);
    }
}
