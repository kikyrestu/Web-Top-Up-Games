<x-layouts.app :title="'Admin Catalog Providers'">
    <div class="grid">
        <div class="panel">
            <h1>Catalog Providers</h1>
            <form method="get" action="{{ route('admin.catalog.providers.index') }}" class="grid" style="grid-template-columns:2fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Code / name">
                </div>
                <div><button class="btn" type="submit">Filter</button></div>
                <div><a class="pill" href="{{ route('admin.catalog.providers.create') }}">+ Tambah Provider</a></div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Nama</th>
                    <th>Status</th>
                    <th>Provider Prices</th>
                    <th>Provider Products</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->code }}</td>
                        <td>{{ $row->name }}</td>
                        <td>
                            @if ($row->is_active)
                                <span class="tag tag-pass">Active</span>
                            @else
                                <span class="tag tag-warn">Inactive</span>
                            @endif
                        </td>
                        <td>{{ (int) $row->prices_count }}</td>
                        <td>{{ (int) $row->provider_products_count }}</td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.catalog.providers.edit', ['provider' => $row->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.catalog.providers.destroy', ['provider' => $row->id]) }}" onsubmit="return confirm('Hapus provider ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada data provider.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
