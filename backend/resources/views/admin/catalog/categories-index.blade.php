<x-layouts.app :title="'Admin Catalog Categories'">
    <div class="grid">
        <div class="panel">
            <h1>Catalog Categories</h1>
            <form method="get" action="{{ route('admin.catalog.categories.index') }}" class="grid" style="grid-template-columns:2fr 1fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Nama / slug">
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
                <div><button class="btn" type="submit">Filter</button></div>
                <div><a class="pill" href="{{ route('admin.catalog.categories.create') }}">+ Tambah Category</a></div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Products</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td>{{ $row->slug }}</td>
                        <td>{{ strtoupper((string) $row->type) }}</td>
                        <td>
                            @if ($row->is_active)
                                <span class="tag tag-pass">Active</span>
                            @else
                                <span class="tag tag-warn">Inactive</span>
                            @endif
                        </td>
                        <td>{{ (int) $row->products_count }}</td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.catalog.categories.edit', ['category' => $row->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.catalog.categories.destroy', ['category' => $row->id]) }}" onsubmit="return confirm('Hapus category ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada data kategori.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
