<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Audit\Services\AuditLogService;
use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class AdminSupportTicketController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $status = strtoupper(trim((string) $request->query('status', '')));
        $priority = strtoupper(trim((string) $request->query('priority', '')));
        $sla = strtoupper(trim((string) $request->query('sla', '')));
        $search = trim((string) $request->query('q', ''));

        $rows = SupportTicket::query()
            ->with(['user:id,name,email,username', 'order:id,order_code', 'assignedAdmin:id,name'])
            ->when($status !== '', static fn ($query) => $query->where('status', $status))
            ->when($priority !== '', static fn ($query) => $query->where('priority', $priority))
            ->when($sla === 'BREACHED', static fn ($query) => $query->whereNotNull('sla_due_at')->where('sla_due_at', '<', now())->whereNotIn('status', ['RESOLVED', 'CLOSED']))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('ticket_code', 'like', '%'.$search.'%')
                        ->orWhere('subject', 'like', '%'.$search.'%')
                        ->orWhereHas('user', static function ($uq) use ($search): void {
                            $uq->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%')
                                ->orWhere('username', 'like', '%'.$search.'%');
                        });
                });
            })
            ->orderByRaw("CASE WHEN status IN ('OPEN','IN_PROGRESS') THEN 0 ELSE 1 END")
            ->orderBy('sla_due_at')
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.support.tickets-index', [
            'rows' => $rows,
            'filters' => [
                'status' => $status,
                'priority' => $priority,
                'sla' => $sla,
                'q' => $search,
            ],
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load([
            'user:id,name,email,username,phone_number',
            'order:id,order_code,status,final_amount',
            'assignedAdmin:id,name',
            'messages:id,support_ticket_id,sender_type,sender_user_id,is_internal,message,sent_at',
            'messages.sender:id,name,role',
        ]);

        return view('admin.support.tickets-show', [
            'ticket' => $ticket,
            'messages' => $ticket->messages->sortBy('sent_at')->values(),
            'admins' => User::query()->where('role', 'admin')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['OPEN', 'IN_PROGRESS', 'WAITING_CUSTOMER', 'RESOLVED', 'CLOSED'])],
            'priority' => ['required', Rule::in(['LOW', 'NORMAL', 'HIGH', 'URGENT'])],
            'assigned_admin_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'sla_due_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $previous = [
            'status' => (string) $ticket->status,
            'priority' => (string) $ticket->priority,
            'assigned_admin_user_id' => $ticket->assigned_admin_user_id,
            'sla_due_at' => $ticket->sla_due_at,
        ];

        $newStatus = strtoupper((string) $validated['status']);

        $ticket->update([
            'status' => $newStatus,
            'priority' => strtoupper((string) $validated['priority']),
            'assigned_admin_user_id' => $validated['assigned_admin_user_id'] ?? null,
            'sla_due_at' => $validated['sla_due_at'] ?? $ticket->sla_due_at,
            'resolved_at' => $newStatus === 'RESOLVED' ? now() : null,
            'closed_at' => $newStatus === 'CLOSED' ? now() : null,
        ]);

        $this->auditLogService->write([
            'event_type' => 'SUPPORT_TICKET_UPDATED_WEB',
            'actor_type' => 'USER',
            'actor_id' => auth()->id(),
            'entity_type' => 'SUPPORT_TICKET',
            'entity_id' => $ticket->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'ticket_code' => $ticket->ticket_code,
                'note' => trim((string) ($validated['note'] ?? '')),
                'previous' => $previous,
                'updated' => [
                    'status' => (string) $ticket->status,
                    'priority' => (string) $ticket->priority,
                    'assigned_admin_user_id' => $ticket->assigned_admin_user_id,
                    'sla_due_at' => $ticket->sla_due_at,
                ],
            ],
            'occurred_at' => now(),
        ]);

        return back()->with('notice', 'Ticket berhasil diperbarui.');
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:4000'],
            'status' => ['nullable', Rule::in(['OPEN', 'IN_PROGRESS', 'WAITING_CUSTOMER', 'RESOLVED', 'CLOSED'])],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        $newStatus = strtoupper((string) ($validated['status'] ?? 'IN_PROGRESS'));
        $isInternal = $request->boolean('is_internal');
        $now = now();

        DB::transaction(function () use ($request, $ticket, $validated, $newStatus, $isInternal, $now): void {
            SupportTicketMessage::query()->create([
                'support_ticket_id' => $ticket->id,
                'sender_type' => 'ADMIN',
                'sender_user_id' => auth()->id(),
                'is_internal' => $isInternal,
                'message' => trim((string) $validated['message']),
                'attachments' => null,
                'sent_at' => $now,
            ]);

            $updates = [
                'status' => $newStatus,
                'last_message_at' => $now,
            ];

            if ($ticket->first_response_at === null && !$isInternal) {
                $updates['first_response_at'] = $now;
            }

            if ($newStatus === 'RESOLVED') {
                $updates['resolved_at'] = $now;
            }

            if ($newStatus === 'CLOSED') {
                $updates['closed_at'] = $now;
            }

            if (!in_array($newStatus, ['RESOLVED', 'CLOSED'], true)) {
                $updates['resolved_at'] = null;
                $updates['closed_at'] = null;
            }

            $ticket->update($updates);
        });

        $this->auditLogService->write([
            'event_type' => 'SUPPORT_TICKET_REPLY_WEB',
            'actor_type' => 'USER',
            'actor_id' => auth()->id(),
            'entity_type' => 'SUPPORT_TICKET',
            'entity_id' => $ticket->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'ticket_code' => $ticket->ticket_code,
                'status' => $newStatus,
                'is_internal' => $isInternal,
                'message_preview' => mb_substr(trim((string) $validated['message']), 0, 180),
            ],
            'occurred_at' => now(),
        ]);

        return back()->with('notice', 'Balasan ticket berhasil dikirim.');
    }
}
