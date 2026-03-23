<x-layouts.app :title="'Admin Promo Campaigns'">
    <div class="grid">
        <div class="panel">
            <h1>Promo Campaign Engine</h1>
            <form method="get" action="{{ route('admin.promo.campaigns.index') }}" class="grid" style="grid-template-columns:2fr 1fr 1fr 1fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Code / nama campaign">
                </div>
                <div>
                    <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="">Semua</option>
                        <option value="VOUCHER" @selected($filters['type'] === 'VOUCHER')>VOUCHER</option>
                        <option value="CASHBACK" @selected($filters['type'] === 'CASHBACK')>CASHBACK</option>
                    </select>
                </div>
                <div>
                    <label for="scope">Scope</label>
                    <select id="scope" name="scope">
                        <option value="">Semua</option>
                        <option value="GLOBAL" @selected($filters['scope'] === 'GLOBAL')>GLOBAL</option>
                        <option value="CATEGORY" @selected($filters['scope'] === 'CATEGORY')>CATEGORY</option>
                        <option value="PRODUCT" @selected($filters['scope'] === 'PRODUCT')>PRODUCT</option>
                    </select>
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua</option>
                        <option value="ACTIVE" @selected($filters['status'] === 'ACTIVE')>Active</option>
                        <option value="INACTIVE" @selected($filters['status'] === 'INACTIVE')>Inactive</option>
                    </select>
                </div>
                <div><button class="btn" type="submit">Filter</button></div>
                <div><a class="pill" href="{{ route('admin.promo.campaigns.create') }}">+ Tambah Campaign</a></div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Code</th>
                    <th>Nama</th>
                    <th>Type</th>
                    <th>Reward</th>
                    <th>Scope</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    @php
                        $scopeTarget = strtoupper((string) $row->scope) === 'PRODUCT'
                            ? (($row->product?->name ?? '-') . ' (' . ($row->product?->sku ?? '-') . ')')
                            : (strtoupper((string) $row->scope) === 'CATEGORY' ? ($row->category?->name ?? '-') : 'Semua Produk');
                    @endphp
                    <tr>
                        <td>{{ $row->code }}</td>
                        <td>
                            <strong>{{ $row->name }}</strong>
                            <div class="muted">Min order: Rp {{ number_format((float) $row->min_order_amount, 0, ',', '.') }}</div>
                        </td>
                        <td>{{ strtoupper((string) $row->campaign_type) }}</td>
                        <td>{{ number_format((float) $row->discount_value, 2, ',', '.') }}{{ strtoupper((string) $row->discount_mode) === 'PERCENTAGE' ? '%' : '' }}</td>
                        <td>{{ strtoupper((string) $row->scope) }}<div class="muted">{{ $scopeTarget }}</div></td>
                        <td>{{ $row->start_at ?: '-' }}<br>{{ $row->end_at ?: '-' }}</td>
                        <td>
                            @if ($row->is_active)
                                <span class="tag tag-pass">Active</span>
                            @else
                                <span class="tag tag-warn">Inactive</span>
                            @endif
                        </td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.promo.campaigns.edit', ['campaign' => $row->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.promo.campaigns.destroy', ['campaign' => $row->id]) }}" onsubmit="return confirm('Hapus promo campaign ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted">Belum ada promo campaign.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
