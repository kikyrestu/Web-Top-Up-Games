<x-layouts.app :title="'Support Ticket ' . $ticket->ticket_code">
    <div class="grid">
        <div class="panel">
            <h1>Ticket {{ $ticket->ticket_code }}</h1>
            <p class="muted">{{ $ticket->subject }}</p>
            <div class="cards" style="margin-top:12px;">
                <div class="card"><div class="k">Status</div><div class="v">{{ $ticket->status }}</div></div>
                <div class="card"><div class="k">Priority</div><div class="v">{{ $ticket->priority }}</div></div>
                <div class="card"><div class="k">SLA Due</div><div class="v" style="font-size:18px;">{{ $ticket->sla_due_at ?: '-' }}</div></div>
            </div>
            <p style="margin-top:10px;" class="muted">Customer: {{ $ticket->user?->name ?: '-' }} ({{ $ticket->user?->email ?: '-' }})</p>
            <p class="muted">Order: {{ $ticket->order?->order_code ?: '-' }} | Category: {{ $ticket->category }}</p>
        </div>

        <div class="panel">
            <h2>Ticket Ops</h2>
            <form method="post" action="{{ route('admin.support.tickets.update', ['ticket' => $ticket->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @method('put')
                <div class="grid" style="grid-template-columns:1fr 1fr 1fr;">
                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            @foreach (['OPEN', 'IN_PROGRESS', 'WAITING_CUSTOMER', 'RESOLVED', 'CLOSED'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $ticket->status) === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority" required>
                            @foreach (['LOW', 'NORMAL', 'HIGH', 'URGENT'] as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', $ticket->priority) === $priority)>{{ $priority }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="assigned_admin_user_id">Assigned Admin</label>
                        <select id="assigned_admin_user_id" name="assigned_admin_user_id">
                            <option value="">-</option>
                            @foreach ($admins as $admin)
                                <option value="{{ (int) $admin->id }}" @selected((int) old('assigned_admin_user_id', $ticket->assigned_admin_user_id) === (int) $admin->id)>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label for="sla_due_at">SLA Due At</label>
                    <input id="sla_due_at" name="sla_due_at" type="datetime-local" value="{{ old('sla_due_at', $ticket->sla_due_at ? $ticket->sla_due_at->format('Y-m-d\\TH:i') : null) }}">
                </div>
                <div>
                    <label for="note">Catatan</label>
                    <textarea id="note" name="note" rows="2" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;">{{ old('note') }}</textarea>
                </div>
                <div><button class="btn" type="submit">Update Ticket</button></div>
            </form>
        </div>

        <div class="panel">
            <h2>Percakapan</h2>
            <div class="grid" style="margin-top:12px;">
                @forelse ($messages as $message)
                    <div style="border:1px solid var(--line); border-radius:10px; padding:10px; background:#0f213f;">
                        <div style="display:flex; justify-content:space-between; gap:10px; margin-bottom:6px;">
                            <strong>{{ $message->sender_type }}{{ $message->sender?->name ? ': '.$message->sender->name : '' }}</strong>
                            <span class="muted">{{ $message->sent_at }}</span>
                        </div>
                        @if ($message->is_internal)
                            <div class="muted" style="color:#ffcc80; margin-bottom:6px;">INTERNAL NOTE</div>
                        @endif
                        <div style="white-space:pre-wrap; word-break:break-word;">{{ $message->message }}</div>
                    </div>
                @empty
                    <p class="muted">Belum ada pesan.</p>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <h2>Kirim Balasan</h2>
            <form method="post" action="{{ route('admin.support.tickets.reply', ['ticket' => $ticket->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="reply_status">Set Status</label>
                        <select id="reply_status" name="status">
                            @foreach (['IN_PROGRESS', 'WAITING_CUSTOMER', 'RESOLVED', 'CLOSED', 'OPEN'] as $status)
                                <option value="{{ $status }}" @selected(old('status', 'IN_PROGRESS') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label style="display:flex; align-items:center; gap:8px; font-weight:700; margin-top:24px;">
                        <input type="checkbox" name="is_internal" value="1" @checked((bool) old('is_internal', false))>
                        Internal note (tidak tampil di customer)
                    </label>
                </div>
                <div>
                    <label for="message">Pesan</label>
                    <textarea id="message" name="message" rows="4" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;" required>{{ old('message') }}</textarea>
                </div>
                <div><button class="btn" type="submit">Kirim Balasan</button></div>
            </form>
        </div>
    </div>
</x-layouts.app>
