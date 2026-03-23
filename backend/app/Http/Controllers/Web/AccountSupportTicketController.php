<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AccountSupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $status = strtoupper(trim((string) $request->query('status', '')));

        $tickets = SupportTicket::query()
            ->with(['order:id,order_code'])
            ->where('user_id', (int) $request->user()->id)
            ->when($status !== '', static fn ($query) => $query->where('status', $status))
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $eligibleOrders = Order::query()
            ->where('user_id', (int) $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get(['id', 'order_code', 'status']);

        return view('account.tickets-index', [
            'tickets' => $tickets,
            'filters' => [
                'status' => $status,
            ],
            'eligibleOrders' => $eligibleOrders,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'category' => ['required', 'in:GENERAL,COMPLAINT,PAYMENT,ORDER,TECHNICAL'],
            'priority' => ['required', 'in:LOW,NORMAL,HIGH,URGENT'],
            'order_code' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'min:5', 'max:4000'],
        ]);

        $userId = (int) $request->user()->id;

        $orderId = null;
        $orderCode = trim((string) ($validated['order_code'] ?? ''));
        if ($orderCode !== '') {
            $order = Order::query()
                ->where('user_id', $userId)
                ->where('order_code', $orderCode)
                ->first();

            if ($order === null) {
                return back()->withErrors([
                    'order_code' => 'Order code tidak ditemukan di akun kamu.',
                ])->withInput();
            }

            $orderId = (int) $order->id;
        }

        $now = now();
        $ticket = DB::transaction(function () use ($validated, $userId, $orderId, $now): SupportTicket {
            $ticket = SupportTicket::query()->create([
                'ticket_code' => 'TKT-'.$now->format('YmdHis').'-'.Str::upper(Str::random(5)),
                'user_id' => $userId,
                'order_id' => $orderId,
                'assigned_admin_user_id' => null,
                'subject' => trim((string) $validated['subject']),
                'category' => strtoupper((string) $validated['category']),
                'priority' => strtoupper((string) $validated['priority']),
                'status' => 'OPEN',
                'sla_due_at' => $this->slaDueAt((string) $validated['priority']),
                'last_message_at' => $now,
                'source_channel' => 'WEB_ACCOUNT',
                'meta' => [
                    'created_by' => 'customer',
                ],
            ]);

            SupportTicketMessage::query()->create([
                'support_ticket_id' => $ticket->id,
                'sender_type' => 'CUSTOMER',
                'sender_user_id' => $userId,
                'is_internal' => false,
                'message' => trim((string) $validated['message']),
                'attachments' => null,
                'sent_at' => $now,
            ]);

            return $ticket;
        });

        return redirect()->route('account.tickets.show', ['ticket' => $ticket->id])->with('notice', 'Tiket support berhasil dibuat.');
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        if ((int) ($ticket->user_id ?? 0) !== (int) $request->user()->id) {
            abort(404);
        }

        $ticket->load([
            'order:id,order_code,status',
            'messages:id,support_ticket_id,sender_type,sender_user_id,is_internal,message,sent_at',
            'messages.sender:id,name,role',
        ]);

        return view('account.tickets-show', [
            'ticket' => $ticket,
            'messages' => $ticket->messages->where('is_internal', false)->sortBy('sent_at')->values(),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        if ((int) ($ticket->user_id ?? 0) !== (int) $request->user()->id) {
            abort(404);
        }

        if (in_array((string) $ticket->status, ['RESOLVED', 'CLOSED'], true)) {
            return back()->withErrors([
                'ticket' => 'Ticket sudah ditutup/diselesaikan dan tidak menerima balasan baru.',
            ]);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:4000'],
        ]);

        $now = now();

        DB::transaction(function () use ($request, $ticket, $validated, $now): void {
            SupportTicketMessage::query()->create([
                'support_ticket_id' => $ticket->id,
                'sender_type' => 'CUSTOMER',
                'sender_user_id' => (int) $request->user()->id,
                'is_internal' => false,
                'message' => trim((string) $validated['message']),
                'attachments' => null,
                'sent_at' => $now,
            ]);

            $ticket->update([
                'status' => 'OPEN',
                'last_message_at' => $now,
                'closed_at' => null,
                'resolved_at' => null,
            ]);
        });

        return back()->with('notice', 'Balasan tiket berhasil dikirim.');
    }

    private function slaDueAt(string $priority)
    {
        $normalized = strtoupper(trim($priority));

        if ($normalized === 'URGENT') {
            return now()->addHours(6);
        }

        if ($normalized === 'HIGH') {
            return now()->addHours(12);
        }

        if ($normalized === 'LOW') {
            return now()->addHours(48);
        }

        return now()->addHours(24);
    }
}
