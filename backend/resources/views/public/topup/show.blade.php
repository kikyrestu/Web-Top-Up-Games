<x-layouts.app :title="'Top Up - ' . $product->name">
    <div class="grid">
        <div class="panel">
            <h1>{{ $product->name }}</h1>
            <p class="muted">Kategori: {{ $product->category?->name }} | SKU: {{ $product->sku }}</p>
            <div style="margin-top:10px;">
                <a class="btn" href="{{ route('storefront.index') }}">Lanjut Checkout</a>
            </div>
        </div>

        <div class="panel">
            <h2>Harga Provider</h2>
            <table>
                <thead>
                <tr><th>Provider</th><th>Base</th><th>Admin Fee</th><th>Komisi</th><th>Status</th></tr>
                </thead>
                <tbody>
                @forelse ($product->providerPrices as $price)
                    <tr>
                        <td>{{ $price->provider?->name ?? '-' }}</td>
                        <td>Rp {{ number_format((float) $price->base_price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format((float) $price->admin_fee, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format((float) $price->commission, 0, ',', '.') }}</td>
                        <td>{{ $price->is_active ? 'AKTIF' : 'NONAKTIF' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada pricing provider.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Ulasan Terbaru</h2>
            @if ($reviews->isEmpty())
                <p class="muted">Belum ada ulasan terverifikasi.</p>
            @else
                @foreach ($reviews as $review)
                    <div style="border-bottom:1px solid var(--line); padding:10px 0;">
                        <div><strong>{{ $review->user?->name ?? 'Guest' }}</strong> • Rating {{ $review->rating }}/5</div>
                        <div class="muted">{{ $review->approved_at ?: $review->created_at }}</div>
                        <p style="margin-top:6px;">{{ $review->content }}</p>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-layouts.app>
