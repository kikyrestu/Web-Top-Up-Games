<x-layouts.app :title="'Admin Customers'">
    <div class="grid">
        <div class="panel">
            <h1>Customer Management</h1>
            <form method="get" action="{{ route('admin.customers.index') }}" class="grid" style="grid-template-columns:2fr 1fr 1fr auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Nama / email / username / phone">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua</option>
                        <option value="ACTIVE" @selected($filters['status'] === 'ACTIVE')>ACTIVE</option>
                        <option value="SUSPENDED" @selected($filters['status'] === 'SUSPENDED')>SUSPENDED</option>
                    </select>
                </div>
                <div>
                    <label for="segment">Segment</label>
                    <select id="segment" name="segment">
                        <option value="">Semua</option>
                        <option value="NEW" @selected($filters['segment'] === 'NEW')>NEW (0 order)</option>
                        <option value="ACTIVE" @selected($filters['segment'] === 'ACTIVE')>ACTIVE (1-4 order)</option>
                        <option value="LOYAL" @selected($filters['segment'] === 'LOYAL')>LOYAL (>=5 order)</option>
                        <option value="HIGH_SPENDER" @selected($filters['segment'] === 'HIGH_SPENDER')>HIGH SPENDER (>= 1jt)</option>
                    </select>
                </div>
                <div><button class="btn" type="submit">Filter</button></div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Customer</th>
                    <th>Kontak</th>
                    <th>Status</th>
                    <th>Orders</th>
                    <th>Total Spend</th>
                    <th>Segment</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    @php
                        $ordersCount = (int) ($row->orders_count ?? 0);
                        $spendTotal = (float) ($row->orders_sum_final_amount ?? 0);
                        $segment = 'NEW';

                        if ($spendTotal >= 1000000) {
                            $segment = 'HIGH_SPENDER';
                        } elseif ($ordersCount >= 5) {
                            $segment = 'LOYAL';
                        } elseif ($ordersCount >= 1) {
                            $segment = 'ACTIVE';
                        }
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $row->name }}</strong>
                            <div class="muted">@{{ $row->username ?: '-' }}</div>
                        </td>
                        <td>
                            <div>{{ $row->email }}</div>
                            <div class="muted">{{ $row->phone_number ?: '-' }}</div>
                        </td>
                        <td>
                            @if (strtoupper((string) ($row->account_status ?? 'ACTIVE')) === 'ACTIVE')
                                <span class="tag tag-pass">ACTIVE</span>
                            @else
                                <span class="tag tag-fail">SUSPENDED</span>
                            @endif
                        </td>
                        <td>{{ $ordersCount }}</td>
                        <td>Rp {{ number_format($spendTotal, 0, ',', '.') }}</td>
                        <td>{{ $segment }}</td>
                        <td><a class="pill" href="{{ route('admin.customers.show', ['user' => $row->id]) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Belum ada data customer.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
