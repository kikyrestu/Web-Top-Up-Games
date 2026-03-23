<x-layouts.app :title="'TopUp Atlas - Order History'">
    <div class="panel">
        <h1>Order History</h1>
        <p class="muted">Menampilkan order yang dibuat dari sesi browser ini.</p>

        @if ($orders->isEmpty())
            <p class="muted" style="margin-top:12px;">Belum ada order di sesi ini. Silakan checkout dulu.</p>
        @else
            <table style="margin-top:12px;">
                <thead>
                <tr>
                    <th>Order Code</th>
                    <th>Product</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th>Created</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>{{ $order->order_code }}</td>
                        <td>{{ $order->product?->name }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ $order->payment?->status ?? '-' }}</td>
                        <td>Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</td>
                        <td>{{ $order->created_at }}</td>
                        <td>
                            <a class="pill" href="{{ route('storefront.track', ['orderCode' => $order->order_code]) }}">Track</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.app>
