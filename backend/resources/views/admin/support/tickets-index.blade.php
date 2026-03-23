<x-layouts.app :title="'Admin Support Tickets'">
    <div class="grid">
        <div class="panel">
            <h1>Support Ticket Console</h1>
            <form method="get" action="{{ route('admin.support.tickets.index') }}" class="grid" style="grid-template-columns:2fr 1fr 1fr 1fr auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="ticket code / subject / customer">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua</option>
                        @foreach (['OPEN', 'IN_PROGRESS', 'WAITING_CUSTOMER', 'RESOLVED', 'CLOSED'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <option value="">Semua</option>
                        @foreach (['LOW', 'NORMAL', 'HIGH', 'URGENT'] as $priority)
                            <option value="{{ $priority }}" @selected($filters['priority'] === $priority)>{{ $priority }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="sla">SLA</label>
                    <select id="sla" name="sla">
                        <option value="">Semua</option>
                        <option value="BREACHED" @selected($filters['sla'] === 'BREACHED')>BREACHED</option>
                    </select>
                </div>
                <div><button class="btn" type="submit">Filter</button></div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr><th>Ticket</th><th>Customer</th><th>Status</th><th>Priority</th><th>SLA Due</th><th>Assigned</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>
                            <strong>{{ $row->ticket_code }}</strong>
                            <div class="muted">{{ $row->subject }}</div>
                        </td>
                        <td>
                            <div>{{ $row->user?->name ?: '-' }}</div>
                            <div class="muted">{{ $row->user?->email ?: '-' }}</div>
                        </td>
                        <td>{{ $row->status }}</td>
                        <td>{{ $row->priority }}</td>
                        <td>
                            {{ $row->sla_due_at ?: '-' }}
                            @if ($row->sla_due_at && $row->sla_due_at->lt(now()) && !in_array((string) $row->status, ['RESOLVED', 'CLOSED'], true))
                                <div class="muted" style="color:#ffb8b8;">SLA BREACHED</div>
                            @endif
                        </td>
                        <td>{{ $row->assignedAdmin?->name ?: '-' }}</td>
                        <td><a class="pill" href="{{ route('admin.support.tickets.show', ['ticket' => $row->id]) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Belum ada support ticket.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
