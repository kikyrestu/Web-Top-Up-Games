<x-layouts.app :title="'Akun - Detail Transaksi ' . $order->order_code">
    <div class="grid">
        <div class="panel">
            <h1>Detail Transaksi</h1>
            <p class="muted">Order: <strong>{{ $order->order_code }}</strong></p>
            <div class="cards" style="margin-top:10px;">
                <div class="card"><div class="k">Order Status</div><div class="v">{{ $order->status }}</div></div>
                <div class="card"><div class="k">Payment</div><div class="v">{{ $order->payment?->status ?? '-' }}</div></div>
                <div class="card"><div class="k">Final Amount</div><div class="v">Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</div></div>
            </div>
        </div>

        <div class="panel">
            <h2>Rincian</h2>
            <table>
                <tr><th>Produk</th><td>{{ $order->product?->name }}</td></tr>
                <tr><th>Customer Target</th><td>{{ $order->customer_target ?: '-' }}</td></tr>
                <tr><th>Base Price</th><td>{{ $order->base_price }}</td></tr>
                <tr><th>Admin Fee</th><td>{{ $order->admin_fee }}</td></tr>
                <tr><th>Margin</th><td>{{ $order->margin }}</td></tr>
                <tr><th>Created At</th><td>{{ $order->created_at }}</td></tr>
            </table>
        </div>

        <div class="panel">
            <h2>Pembayaran</h2>
            @if ($order->payment)
                <table>
                    <tr><th>Gateway</th><td>{{ $order->payment->gateway }}</td></tr>
                    <tr><th>Reference</th><td>{{ $order->payment->gateway_reference }}</td></tr>
                    <tr><th>Method</th><td>{{ $order->payment->method ?: '-' }}</td></tr>
                    <tr><th>Expired At</th><td>{{ $order->payment->expired_at ?: '-' }}</td></tr>
                </table>
                @if (!empty($paymentMeta['pay_url']))
                    <div style="margin-top:10px;"><a class="btn" href="{{ $paymentMeta['pay_url'] }}" target="_blank" rel="noopener">Buka Pembayaran</a></div>
                @endif
            @else
                <p class="muted">Belum ada payment.</p>
            @endif
        </div>
    </div>
</x-layouts.app>
