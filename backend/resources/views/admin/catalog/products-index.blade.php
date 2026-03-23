<x-layouts.app :title="'Admin Catalog Products'">
    <div class="grid">
        <div class="panel">
            <h1>Catalog Products</h1>
            <form method="get" action="{{ route('admin.catalog.products.index') }}" class="grid" style="grid-template-columns:2fr 1fr 1fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Nama / slug / sku">
                </div>
                <div>
                    <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="">Semua</option>
                        @foreach (['TOPUP', 'PPOB'] as $type)
                            <option value="{{ $type }}" @selected($filters['type'] === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="0">Semua</option>
                        @foreach ($categories as $category)
                            <option value="{{ (int) $category->id }}" @selected((int) $filters['category_id'] === (int) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><button class="btn" type="submit">Filter</button></div>
                <div><a class="pill" href="{{ route('admin.catalog.products.create') }}">+ Tambah Product</a></div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Provider Prices</th>
                    <th>Provider Products</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->name }}<br><span class="muted">{{ $row->slug }}</span></td>
                        <td>{{ $row->sku }}</td>
                        <td>{{ $row->category?->name ?? '-' }}</td>
                        <td>{{ strtoupper((string) $row->type) }}</td>
                        <td>
                            @if ($row->is_active)
                                <span class="tag tag-pass">Active</span>
                            @else
                                <span class="tag tag-warn">Inactive</span>
                            @endif
                        </td>
                        <td>{{ (int) $row->provider_prices_count }}</td>
                        <td>{{ (int) $row->provider_products_count }}</td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.catalog.products.edit', ['product' => $row->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.catalog.products.destroy', ['product' => $row->id]) }}" onsubmit="return confirm('Hapus product ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted">Belum ada data produk.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
