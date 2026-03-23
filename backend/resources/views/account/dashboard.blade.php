<x-layouts.app :title="'Akun Saya'">
    <div class="grid">
        <div class="panel">
            <h1>Akun Saya</h1>
            <p class="muted">Ringkasan transaksi dan akses cepat menu akun.</p>
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
                <a class="pill" href="{{ route('account.transactions') }}">Riwayat Transaksi</a>
                <a class="pill" href="{{ route('account.profile') }}">Profil</a>
                <a class="pill" href="{{ route('account.reviews') }}">Ulasan Saya</a>
                <form method="post" action="{{ route('account.logout') }}">@csrf<button type="submit" class="pill">Logout</button></form>
            </div>

            <div class="cards" style="margin-top:14px;">
                <div class="card"><div class="k">Total Order</div><div class="v">{{ (int) $summary['total_orders'] }}</div></div>
                <div class="card"><div class="k">Order Success</div><div class="v">{{ (int) $summary['success_orders'] }}</div></div>
                <div class="card"><div class="k">Order Failed</div><div class="v">{{ (int) $summary['failed_orders'] }}</div></div>
            </div>
        </div>

        <div class="panel">
            <h2>Transaksi Terbaru</h2>
            <table>
                <thead>
                <tr><th>Order</th><th>Produk</th><th>Status</th><th>Payment</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($recentOrders as $order)
                    <tr>
                        <td>{{ $order->order_code }}</td>
                        <td>{{ $order->product?->name }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ $order->payment?->status ?? '-' }}</td>
                        <td><a class="pill" href="{{ route('account.transactions.show', ['orderCode' => $order->order_code]) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada transaksi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
