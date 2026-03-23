<x-layouts.app :title="'Admin Nominal Mappings'">
    <div class="grid">
        <div class="panel">
            <h1>Nominal Mappings (Provider Product)</h1>
            <form method="get" action="{{ route('admin.nominal.mappings.index') }}" class="grid" style="grid-template-columns:1.5fr 1.5fr 2fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="provider_id">Provider</label>
                    <select id="provider_id" name="provider_id">
                        <option value="0">Semua</option>
                        @foreach ($providers as $provider)
                            <option value="{{ (int) $provider->id }}" @selected((int) $filters['provider_id'] === (int) $provider->id)>{{ $provider->code }} - {{ $provider->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="product_id">Product</label>
                    <select id="product_id" name="product_id">
                        <option value="0">Semua</option>
                        @foreach ($products as $product)
                            <option value="{{ (int) $product->id }}" @selected((int) $filters['product_id'] === (int) $product->id)>{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Provider product code / name">
                </div>
                <div><button class="btn" type="submit">Filter</button></div>
                <div><a class="pill" href="{{ route('admin.nominal.mappings.create') }}">+ Tambah Mapping</a></div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Provider</th>
                    <th>Product</th>
                    <th>Provider Product</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->provider?->code }} - {{ $row->provider?->name }}</td>
                        <td>{{ $row->product?->name }}<br><span class="muted">{{ $row->product?->sku }}</span></td>
                        <td>{{ $row->provider_product_name }}<br><span class="muted">{{ $row->provider_product_code }}</span></td>
                        <td>
                            @if ($row->is_available)
                                <span class="tag tag-pass">Available</span>
                            @else
                                <span class="tag tag-warn">Unavailable</span>
                            @endif
                        </td>
                        <td>{{ $row->updated_at }}</td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.nominal.mappings.edit', ['mapping' => $row->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.nominal.mappings.destroy', ['mapping' => $row->id]) }}" onsubmit="return confirm('Hapus mapping ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada data mapping provider product.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
