<x-layouts.app :title="'Admin Order ' . $order->order_code">
    <div class="grid">
        <div class="panel">
            <h1>Order Detail</h1>
            <p class="muted">Kode: <strong>{{ $order->order_code }}</strong></p>

            <div class="cards" style="margin-top:12px;">
                <div class="card"><div class="k">Order Status</div><div class="v">{{ $order->status }}</div></div>
                <div class="card"><div class="k">Payment Status</div><div class="v">{{ $order->payment?->status ?? '-' }}</div></div>
                <div class="card"><div class="k">Final Amount</div><div class="v">Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</div></div>
            </div>

            @if ($order->status === 'FAILED')
                <form method="post" action="{{ route('admin.orders.reprocess', ['orderCode' => $order->order_code]) }}" style="margin-top:14px;">
                    @csrf
                    <button type="submit" class="btn">Reprocess Order</button>
                </form>
            @endif
        </div>

        <div class="panel">
            <h2>Ringkasan</h2>
            <table>
                <tr><th>Product</th><td>{{ $order->product?->name }}</td></tr>
                <tr><th>Type</th><td>{{ $order->product_type }}</td></tr>
                <tr><th>Customer Target</th><td>{{ $order->customer_target ?: '-' }}</td></tr>
                <tr><th>Base Price</th><td>{{ $order->base_price }}</td></tr>
                <tr><th>Admin Fee</th><td>{{ $order->admin_fee }}</td></tr>
                <tr><th>Margin</th><td>{{ $order->margin }}</td></tr>
                <tr><th>Created At</th><td>{{ $order->created_at }}</td></tr>
            </table>
        </div>

        <div class="panel">
            <h2>Payment</h2>
            @if ($order->payment)
                <table>
                    <tr><th>Gateway</th><td>{{ $order->payment->gateway }}</td></tr>
                    <tr><th>Gateway Ref</th><td>{{ $order->payment->gateway_reference }}</td></tr>
                    <tr><th>Status</th><td>{{ $order->payment->status }}</td></tr>
                    <tr><th>Method</th><td>{{ $order->payment->method ?: '-' }}</td></tr>
                    <tr><th>Paid At</th><td>{{ $order->payment->paid_at ?: '-' }}</td></tr>
                    <tr><th>Expired At</th><td>{{ $order->payment->expired_at ?: '-' }}</td></tr>
                </table>
            @else
                <p class="muted">Belum ada data payment.</p>
            @endif
        </div>

        <div class="panel">
            <h2>Provider Attempts</h2>
            <table>
                <thead>
                <tr><th>No</th><th>Provider</th><th>Status</th><th>Ref</th><th>At</th><th>Payload</th></tr>
                </thead>
                <tbody>
                @forelse ($order->providerAttempts->sortBy('attempt_no') as $attempt)
                    <tr>
                        <td>#{{ $attempt->attempt_no }}</td>
                        <td>{{ $attempt->provider?->code }} - {{ $attempt->provider?->name }}</td>
                        <td>{{ $attempt->status }}</td>
                        <td>{{ $attempt->provider_ref ?: '-' }}</td>
                        <td>{{ $attempt->attempted_at ?: '-' }}</td>
                        <td>
                            <details>
                                <summary>Request</summary>
                                <pre style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($attempt->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                            <details style="margin-top:6px;">
                                <summary>Response</summary>
                                <pre style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($attempt->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada provider attempts.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
