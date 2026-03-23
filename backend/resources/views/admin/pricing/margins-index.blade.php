<x-layouts.app :title="'Admin Pricing Rules'">
    <div class="grid">
        <div class="panel">
            <h1>Pricing Rules (Margins)</h1>
            <p class="muted">Prioritas eksekusi margin: Product > Category > Global.</p>
            <form method="get" action="{{ route('admin.pricing.margins.index') }}" class="grid" style="grid-template-columns:1fr 1fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="scope">Scope</label>
                    <select id="scope" name="scope">
                        <option value="">Semua</option>
                        <option value="PRODUCT" @selected($filters['scope'] === 'PRODUCT')>PRODUCT</option>
                        <option value="CATEGORY" @selected($filters['scope'] === 'CATEGORY')>CATEGORY</option>
                        <option value="GLOBAL" @selected($filters['scope'] === 'GLOBAL')>GLOBAL</option>
                    </select>
                </div>
                <div>
                    <label for="mode">Mode</label>
                    <select id="mode" name="mode">
                        <option value="">Semua</option>
                        <option value="FLAT" @selected($filters['mode'] === 'FLAT')>FLAT</option>
                        <option value="PERCENTAGE" @selected($filters['mode'] === 'PERCENTAGE')>PERCENTAGE</option>
                    </select>
                </div>
                <div><button class="btn" type="submit">Filter</button></div>
                <div><a class="pill" href="{{ route('admin.pricing.margins.create') }}">+ Tambah Rule</a></div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Scope</th>
                    <th>Target</th>
                    <th>Mode</th>
                    <th>Value</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    @php
                        $scope = $row->product_id ? 'PRODUCT' : ($row->category_id ? 'CATEGORY' : 'GLOBAL');
                        $target = $row->product_id ? (($row->product?->name ?? '-') . ' (' . ($row->product?->sku ?? '-') . ')') : ($row->category_id ? ($row->category?->name ?? '-') : 'Semua Produk');
                    @endphp
                    <tr>
                        <td>{{ $scope }}</td>
                        <td>{{ $target }}</td>
                        <td>{{ strtoupper((string) $row->mode) }}</td>
                        <td>{{ number_format((float) $row->value, 2, ',', '.') }}{{ strtoupper((string) $row->mode) === 'PERCENTAGE' ? '%' : '' }}</td>
                        <td>
                            @if ($row->is_active)
                                <span class="tag tag-pass">Active</span>
                            @else
                                <span class="tag tag-warn">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $row->updated_at }}</td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.pricing.margins.edit', ['margin' => $row->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.pricing.margins.destroy', ['margin' => $row->id]) }}" onsubmit="return confirm('Hapus rule margin ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Belum ada data pricing rule margin.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
