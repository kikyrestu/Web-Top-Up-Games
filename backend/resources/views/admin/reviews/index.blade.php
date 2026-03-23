<x-layouts.app :title="'Admin Review Moderation'">
    <div class="grid">
        <div class="panel">
            <h1>Review Moderation</h1>
            <form method="get" action="{{ route('admin.reviews.index') }}" class="grid" style="grid-template-columns:2fr 1fr auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Isi review / nama produk / order code">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        @foreach (['PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'ALL'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="btn" type="submit">Filter</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Review</th>
                    <th>User</th>
                    <th>Produk</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Aksi Moderasi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td>
                            <strong>{{ (int) $review->rating }}/5</strong><br>
                            <span class="muted">{{ $review->content }}</span>
                        </td>
                        <td>
                            {{ $review->user?->name ?: 'Guest' }}<br>
                            <span class="muted">{{ $review->user?->email ?: '-' }}</span>
                        </td>
                        <td>{{ $review->product?->name ?: '-' }}</td>
                        <td>{{ $review->order?->order_code ?: '-' }}</td>
                        <td>
                            @if ($review->status === 'APPROVED')
                                <span class="tag tag-pass">APPROVED</span>
                            @elseif ($review->status === 'REJECTED')
                                <span class="tag tag-fail">REJECTED</span>
                            @else
                                <span class="tag tag-warn">PENDING</span>
                            @endif
                        </td>
                        <td>
                            <div class="grid" style="grid-template-columns:1fr; gap:8px; min-width:230px;">
                                <form method="post" action="{{ route('admin.reviews.approve', ['review' => $review->id]) }}" class="grid" style="gap:6px;">
                                    @csrf
                                    <input type="text" name="reason" placeholder="Catatan approve (opsional)">
                                    <button class="btn" type="submit">Approve</button>
                                </form>
                                <form method="post" action="{{ route('admin.reviews.reject', ['review' => $review->id]) }}" class="grid" style="gap:6px;">
                                    @csrf
                                    <input type="text" name="reason" placeholder="Alasan reject (opsional)">
                                    <button class="btn btn-ghost" type="submit">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada review untuk dimoderasi.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">
                {{ $reviews->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
