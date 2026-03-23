<x-layouts.app :title="'Top Up Games'">
    <div class="grid">
        <div class="panel">
            <h1>Top Up Games</h1>
            <p class="muted">Pilih game favorit, lanjut ke detail nominal, lalu checkout instan.</p>
        </div>

        <div class="panel">
            <h2>Kategori Top Up</h2>
            <div class="cards" style="margin-top:10px;">
                @forelse ($categories as $category)
                    <div class="card">
                        <div class="k">Kategori</div>
                        <div class="v" style="font-size:20px;">{{ $category->name }}</div>
                        <div class="muted">{{ (int) $category->active_products_count }} produk aktif</div>
                    </div>
                @empty
                    <p class="muted">Belum ada kategori top up aktif.</p>
                @endforelse
            </div>
        </div>

        <div class="panel">
            <h2>Produk Top Up</h2>
            <table>
                <thead>
                <tr><th>Produk</th><th>Kategori</th><th>Harga Mulai</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category?->name ?? '-' }}</td>
                        <td>Rp {{ number_format((float) ($product->lowest_price ?? 0), 0, ',', '.') }}</td>
                        <td><a class="pill" href="{{ route('public.topup.show', ['gameSlug' => $product->slug]) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Belum ada produk top up.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
