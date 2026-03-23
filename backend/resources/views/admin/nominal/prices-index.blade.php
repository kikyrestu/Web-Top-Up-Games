<x-layouts.app :title="'Admin Nominal Prices'">
    <div class="grid">
        <div class="panel">
            <h1>Nominal Prices (Provider Price)</h1>
            <form method="get" action="{{ route('admin.nominal.prices.index') }}" class="grid" style="grid-template-columns:1.6fr 1.6fr auto auto; align-items:end; margin-top:12px;">
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
                <div><button class="btn" type="submit">Filter</button></div>
                <div><a class="pill" href="{{ route('admin.nominal.prices.create') }}">+ Tambah Price</a></div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Provider</th>
                    <th>Product</th>
                    <th>Base Price</th>
                    <th>Admin Fee</th>
                    <th>Commission</th>
                    <th>Status</th>
                    <th>Provider Updated</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->provider?->code }} - {{ $row->provider?->name }}</td>
                        <td>{{ $row->product?->name }}<br><span class="muted">{{ $row->product?->sku }}</span></td>
                        <td>{{ number_format((float) $row->base_price, 2, ',', '.') }}</td>
                        <td>{{ number_format((float) $row->admin_fee, 2, ',', '.') }}</td>
                        <td>{{ number_format((float) $row->commission, 2, ',', '.') }}</td>
                        <td>
                            @if ($row->is_active)
                                <span class="tag tag-pass">Active</span>
                            @else
                                <span class="tag tag-warn">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $row->provider_updated_at ?: '-' }}</td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.nominal.prices.edit', ['price' => $row->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.nominal.prices.destroy', ['price' => $row->id]) }}" onsubmit="return confirm('Hapus provider price ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted">Belum ada data provider price.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
