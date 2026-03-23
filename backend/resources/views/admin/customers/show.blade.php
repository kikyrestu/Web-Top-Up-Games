<x-layouts.app :title="'Customer ' . $row->name">
    <div class="grid">
        <div class="panel">
            <h1>Customer Detail</h1>
            <p class="muted">ID: {{ (int) $row->id }}</p>
            <div class="cards" style="margin-top:12px;">
                <div class="card"><div class="k">Orders</div><div class="v">{{ (int) $summary['orders_total'] }}</div></div>
                <div class="card"><div class="k">Success</div><div class="v">{{ (int) $summary['orders_success'] }}</div></div>
                <div class="card"><div class="k">Spend</div><div class="v">Rp {{ number_format((float) $summary['spend_total'], 0, ',', '.') }}</div></div>
            </div>
        </div>

        <div class="panel">
            <h2>Profile Ops</h2>
            <form method="post" action="{{ route('admin.customers.update', ['user' => $row->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @method('put')

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="name">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $row->name) }}" required>
                    </div>
                    <div>
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" value="{{ old('username', $row->username) }}" required>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $row->email) }}" required>
                    </div>
                    <div>
                        <label for="phone_number">Phone Number</label>
                        <input id="phone_number" name="phone_number" type="text" value="{{ old('phone_number', $row->phone_number) }}" placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="account_status">Account Status</label>
                        <select id="account_status" name="account_status" required>
                            <option value="ACTIVE" @selected(old('account_status', strtoupper((string) ($row->account_status ?: 'ACTIVE'))) === 'ACTIVE')>ACTIVE</option>
                            <option value="SUSPENDED" @selected(old('account_status', strtoupper((string) ($row->account_status ?: 'ACTIVE'))) === 'SUSPENDED')>SUSPENDED</option>
                        </select>
                    </div>
                    <div>
                        <label for="password">Reset Password (opsional)</label>
                        <input id="password" name="password" type="password" placeholder="Isi jika ingin ganti password">
                    </div>
                </div>

                <div>
                    <label for="note">Catatan Admin</label>
                    <textarea id="note" name="note" rows="3" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;">{{ old('note') }}</textarea>
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="revoke_sessions" value="1" @checked((bool) old('revoke_sessions', false))>
                    Revoke semua sesi login customer
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan Profile Ops</button>
                    <a class="pill" href="{{ route('admin.customers.index') }}">Kembali</a>
                </div>
            </form>
        </div>

        <div class="panel">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                <tr><th>Order</th><th>Status</th><th>Produk</th><th>Amount</th><th>Tanggal</th></tr>
                </thead>
                <tbody>
                @forelse ($recentOrders as $order)
                    <tr>
                        <td><a class="pill" href="{{ route('admin.orders.show', ['orderCode' => $order->order_code]) }}">{{ $order->order_code }}</a></td>
                        <td>{{ $order->status }}</td>
                        <td>{{ $order->product?->name ?: '-' }}</td>
                        <td>Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</td>
                        <td>{{ $order->created_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada order.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Recent Reviews</h2>
            <table>
                <thead>
                <tr><th>Produk</th><th>Rating</th><th>Status</th><th>Konten</th><th>Tanggal</th></tr>
                </thead>
                <tbody>
                @forelse ($recentReviews as $review)
                    <tr>
                        <td>{{ $review->product?->name ?: '-' }}</td>
                        <td>{{ (int) $review->rating }}</td>
                        <td>{{ $review->status }}</td>
                        <td>{{ \Illuminate\Support\Str::limit((string) $review->content, 120) }}</td>
                        <td>{{ $review->created_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada review.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
