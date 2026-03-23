<x-layouts.app :title="'PPOB - ' . $category->name">
    <div class="grid">
        <div class="panel">
            <h1>{{ $category->name }}</h1>
            <p class="muted">Tipe kategori: {{ $category->type }}.</p>
        </div>

        <div class="panel">
            <h2>Produk dalam kategori ini</h2>
            <table>
                <thead>
                <tr><th>Produk</th><th>Harga Mulai</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>Rp {{ number_format((float) ($product->lowest_price ?? 0), 0, ',', '.') }}</td>
                        <td><a class="pill" href="{{ route('storefront.index') }}">Checkout</a></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="muted">Belum ada produk aktif.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
