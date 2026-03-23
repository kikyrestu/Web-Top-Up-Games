<x-layouts.app :title="'Review Detail #'.$review->id">
    <div class="grid">
        <div class="panel">
            <h1>Review Detail</h1>
            <div class="muted" style="margin-bottom:10px;">
                Review ID #{{ (int) $review->id }}
            </div>
            <div class="grid" style="grid-template-columns:1fr 1fr; gap:10px;">
                <div class="card">
                    <div class="k">User</div>
                    <div class="v" style="font-size:16px;">{{ $review->user?->name ?: 'Guest' }}</div>
                    <div class="muted">{{ $review->user?->email ?: '-' }}</div>
                </div>
                <div class="card">
                    <div class="k">Order</div>
                    <div class="v" style="font-size:16px;">{{ $review->order?->order_code ?: '-' }}</div>
                    <div class="muted">{{ $review->order?->status ?: '-' }}</div>
                </div>
                <div class="card">
                    <div class="k">Produk</div>
                    <div class="v" style="font-size:16px;">{{ $review->product?->name ?: '-' }}</div>
                    <div class="muted">Slug: {{ $review->product?->slug ?: '-' }}</div>
                </div>
                <div class="card">
                    <div class="k">Status Review</div>
                    <div class="v" style="font-size:16px;">{{ $review->status }}</div>
                    <div class="muted">Approved at: {{ $review->approved_at ?: '-' }}</div>
                </div>
            </div>

            <div class="panel" style="margin-top:12px; background:#fbfcfb;">
                <div class="k">Rating & Content</div>
                <div style="font-weight:800; margin:8px 0;">{{ (int) $review->rating }}/5</div>
                <div>{{ $review->content }}</div>
            </div>

            <div style="display:flex; gap:10px; margin-top:12px;">
                <form method="post" action="{{ route('admin.reviews.approve', ['review' => $review->id]) }}" class="grid" style="grid-template-columns:1fr auto; gap:8px; flex:1;">
                    @csrf
                    <input type="text" name="reason" placeholder="Catatan approve (opsional)">
                    <button class="btn" type="submit">Approve</button>
                </form>
                <form method="post" action="{{ route('admin.reviews.reject', ['review' => $review->id]) }}" class="grid" style="grid-template-columns:1fr auto; gap:8px; flex:1;">
                    @csrf
                    <input type="text" name="reason" placeholder="Alasan reject (opsional)">
                    <button class="btn btn-ghost" type="submit">Reject</button>
                </form>
                <a class="pill" href="{{ route('admin.reviews.index') }}">Kembali</a>
            </div>
        </div>

        <div class="panel">
            <h2>Moderation History</h2>
            <table>
                <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Aksi</th>
                    <th>Admin</th>
                    <th>Reason</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($moderationHistory as $item)
                    <tr>
                        <td>{{ $item->moderated_at ?: $item->created_at }}</td>
                        <td>{{ $item->action }}</td>
                        <td>{{ $item->adminUser?->name ?: '-' }}</td>
                        <td>{{ $item->reason ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Belum ada riwayat moderasi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
