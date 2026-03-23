<x-layouts.app :title="'Ticket ' . $ticket->ticket_code">
    <div class="grid">
        <div class="panel">
            <h1>Ticket {{ $ticket->ticket_code }}</h1>
            <div class="cards" style="margin-top:12px;">
                <div class="card"><div class="k">Status</div><div class="v">{{ $ticket->status }}</div></div>
                <div class="card"><div class="k">Priority</div><div class="v">{{ $ticket->priority }}</div></div>
                <div class="card"><div class="k">SLA Due</div><div class="v" style="font-size:18px;">{{ $ticket->sla_due_at ?: '-' }}</div></div>
            </div>
            <p style="margin-top:12px;"><strong>{{ $ticket->subject }}</strong></p>
            <p class="muted">Category: {{ $ticket->category }} | Order: {{ $ticket->order?->order_code ?: '-' }}</p>
        </div>

        <div class="panel">
            <h2>Percakapan</h2>
            <div class="grid" style="margin-top:12px;">
                @forelse ($messages as $message)
                    <div style="border:1px solid var(--line); border-radius:10px; padding:10px; background:#0f213f;">
                        <div style="display:flex; justify-content:space-between; gap:10px; margin-bottom:6px;">
                            <strong>{{ $message->sender_type === 'ADMIN' ? ('Admin: '.($message->sender?->name ?: '-')) : 'Kamu' }}</strong>
                            <span class="muted">{{ $message->sent_at }}</span>
                        </div>
                        <div style="white-space:pre-wrap; word-break:break-word;">{{ $message->message }}</div>
                    </div>
                @empty
                    <p class="muted">Belum ada percakapan.</p>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <h2>Kirim Balasan</h2>
            @if (in_array((string) $ticket->status, ['RESOLVED', 'CLOSED'], true))
                <p class="muted">Ticket ini sudah selesai/ditutup.</p>
            @else
                <form method="post" action="{{ route('account.tickets.reply', ['ticket' => $ticket->id]) }}" class="grid" style="margin-top:12px;">
                    @csrf
                    <div>
                        <label for="message">Pesan</label>
                        <textarea id="message" name="message" rows="4" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;" required>{{ old('message') }}</textarea>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button class="btn" type="submit">Kirim Balasan</button>
                        <a class="pill" href="{{ route('account.tickets.index') }}">Kembali</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-layouts.app>
