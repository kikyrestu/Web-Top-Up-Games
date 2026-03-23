<x-layouts.app :title="'Akun - Ulasan Saya'">
    <div class="grid">
        <div class="panel">
            <h1>Kirim Ulasan</h1>
            <form method="post" action="{{ route('account.reviews.store') }}" class="grid" style="grid-template-columns: 2fr 120px; gap:12px; align-items:end; margin-top:10px;">
                @csrf
                <div>
                    <label for="order_code">Order Sukses</label>
                    <select id="order_code" name="order_code" required>
                        <option value="">Pilih order</option>
                        @foreach ($eligibleOrders as $order)
                            <option value="{{ $order->order_code }}" @selected(old('order_code') === $order->order_code)>
                                {{ $order->order_code }} - {{ $order->product?->name ?: '-' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="muted" style="margin-top:6px; font-size:13px;">
                        Hanya order status SUCCESS yang belum pernah direview yang bisa dipilih.
                    </div>
                </div>
                <div>
                    <label for="rating">Rating</label>
                    <select id="rating" name="rating" required>
                        @foreach ([5,4,3,2,1] as $score)
                            <option value="{{ $score }}" @selected((int) old('rating', 5) === $score)>{{ $score }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="grid-column:1 / -1;">
                    <label for="content">Ulasan</label>
                    <textarea id="content" name="content" rows="4" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#fff;" required>{{ old('content') }}</textarea>
                </div>
                <div style="grid-column:1 / -1;">
                    <button class="btn" type="submit">Kirim Ulasan</button>
                </div>
            </form>
        </div>

        <div class="panel">
        <h1>Ulasan Saya</h1>
        <table>
            <thead>
            <tr><th>Produk</th><th>Rating</th><th>Status</th><th>Konten</th><th>Tanggal</th></tr>
            </thead>
            <tbody>
            @forelse ($reviews as $review)
                <tr>
                    <td>{{ $review->product?->name ?? '-' }}</td>
                    <td>{{ $review->rating }}/5</td>
                    <td>{{ $review->status }}</td>
                    <td>{{ $review->content }}</td>
                    <td>{{ $review->created_at }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Belum ada ulasan.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px;">{{ $reviews->links() }}</div>
        </div>
    </div>
</x-layouts.app>
