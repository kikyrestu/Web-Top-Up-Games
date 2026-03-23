<x-layouts.app :title="'Akun - Support Tickets'">
    <div class="grid">
        <div class="panel">
            <h1>Support Tickets</h1>
            <p class="muted">Kelola tiket bantuan, komplain, dan status SLA respons.</p>
            <form method="get" action="{{ route('account.tickets.index') }}" class="grid" style="grid-template-columns:1fr auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua</option>
                        @foreach (['OPEN', 'IN_PROGRESS', 'WAITING_CUSTOMER', 'RESOLVED', 'CLOSED'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div><button class="btn" type="submit">Filter</button></div>
            </form>
        </div>

        <div class="panel">
            <h2>Buat Ticket Baru</h2>
            <form method="post" action="{{ route('account.tickets.store') }}" class="grid" style="margin-top:12px;">
                @csrf
                <div>
                    <label for="subject">Subject</label>
                    <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required>
                </div>
                <div class="grid" style="grid-template-columns:1fr 1fr 1fr;">
                    <div>
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            @foreach (['GENERAL', 'COMPLAINT', 'PAYMENT', 'ORDER', 'TECHNICAL'] as $category)
                                <option value="{{ $category }}" @selected(old('category', 'GENERAL') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" required>
                            @foreach (['LOW', 'NORMAL', 'HIGH', 'URGENT'] as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', 'NORMAL') === $priority)>{{ $priority }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="order_code">Order Code (opsional)</label>
                        <select id="order_code" name="order_code">
                            <option value="">-</option>
                            @foreach ($eligibleOrders as $order)
                                <option value="{{ $order->order_code }}" @selected(old('order_code') === $order->order_code)>{{ $order->order_code }} ({{ $order->status }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="message">Pesan</label>
                    <textarea id="message" name="message" rows="4" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;" required>{{ old('message') }}</textarea>
                </div>
                <div><button class="btn" type="submit">Kirim Ticket</button></div>
            </form>
        </div>

        <div class="panel">
            <h2>Daftar Ticket</h2>
            <table>
                <thead>
                <tr><th>Code</th><th>Subject</th><th>Status</th><th>Priority</th><th>SLA Due</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->ticket_code }}</td>
                        <td>{{ $ticket->subject }}<div class="muted">{{ $ticket->category }}</div></td>
                        <td>{{ $ticket->status }}</td>
                        <td>{{ $ticket->priority }}</td>
                        <td>{{ $ticket->sla_due_at ?: '-' }}</td>
                        <td><a class="pill" href="{{ route('account.tickets.show', ['ticket' => $ticket->id]) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada ticket.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $tickets->links() }}</div>
        </div>
    </div>
</x-layouts.app>
