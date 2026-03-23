<x-layouts.app :title="'TopUp Atlas - Tracking ' . $order->order_code">
    <div class="grid">
        <div class="panel">
            <h1>Tracking Order</h1>
            <p class="muted">Kode order: <strong>{{ $order->order_code }}</strong></p>

            <div class="cards" style="margin-top:14px;">
                <div class="card">
                    <div class="k">Order Status</div>
                    <div class="v">{{ $order->status }}</div>
                </div>
                <div class="card">
                    <div class="k">Final Amount</div>
                    <div class="v">Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</div>
                </div>
                <div class="card">
                    <div class="k">Product</div>
                    <div class="v" style="font-size:20px;">{{ $order->product?->name }}</div>
                </div>
            </div>
        </div>

        <div class="panel">
            <h2>Payment</h2>
            @if ($order->payment)
                <table>
                    <tr><th>Gateway</th><td>{{ $order->payment->gateway }}</td></tr>
                    <tr><th>Reference</th><td>{{ $order->payment->gateway_reference }}</td></tr>
                    <tr><th>Status</th><td>{{ $order->payment->status }}</td></tr>
                    <tr><th>Method</th><td>{{ $order->payment->method ?: '-' }}</td></tr>
                    <tr><th>Expired At</th><td>{{ $order->payment->expired_at ?: '-' }}</td></tr>
                </table>

                @if (!empty($paymentMeta['pay_url']))
                    <div style="margin-top:14px;">
                        <a class="btn" href="{{ $paymentMeta['pay_url'] }}" target="_blank" rel="noopener">Buka Halaman Pembayaran</a>
                    </div>
                @endif
            @else
                <p class="muted">Belum ada data payment.</p>
            @endif
        </div>

        <div class="panel">
            <h2>Provider Attempts</h2>
            @if ($order->providerAttempts->isEmpty())
                <p class="muted">Belum ada attempt fulfillment.</p>
            @else
                <table>
                    <thead>
                    <tr>
                        <th>Attempt</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Ref</th>
                        <th>At</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($order->providerAttempts as $attempt)
                        <tr>
                            <td>#{{ $attempt->attempt_no }}</td>
                            <td>{{ $attempt->provider?->code }} - {{ $attempt->provider?->name }}</td>
                            <td>{{ $attempt->status }}</td>
                            <td>{{ $attempt->provider_ref ?: '-' }}</td>
                            <td>{{ $attempt->attempted_at ?: '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.app>
