<x-layouts.app :title="'Akun - Transaksi'">
    <div class="panel">
        <h1>Riwayat Transaksi</h1>
        <table>
            <thead>
            <tr><th>Order</th><th>Produk</th><th>Status</th><th>Payment</th><th>Amount</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->order_code }}</td>
                    <td>{{ $order->product?->name }}</td>
                    <td>{{ $order->status }}</td>
                    <td>{{ $order->payment?->status ?? '-' }}</td>
                    <td>Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</td>
                    <td><a class="pill" href="{{ route('account.transactions.show', ['orderCode' => $order->order_code]) }}">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:12px;">{{ $orders->links() }}</div>
    </div>
</x-layouts.app>
