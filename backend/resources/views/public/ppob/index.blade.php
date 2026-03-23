<x-layouts.app :title="'PPOB Categories'">
    <div class="grid">
        <div class="panel">
            <h1>PPOB</h1>
            <p class="muted">Bayar tagihan, pulsa, data, dan multifinance dalam satu alur checkout.</p>
        </div>

        <div class="panel">
            <h2>Kategori PPOB</h2>
            <table>
                <thead>
                <tr><th>Kategori</th><th>Tipe</th><th>Produk Aktif</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->type }}</td>
                        <td>{{ (int) $category->active_products_count }}</td>
                        <td><a class="pill" href="{{ route('public.ppob.show', ['categorySlug' => $category->slug]) }}">Lihat Produk</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Belum ada kategori PPOB aktif.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
