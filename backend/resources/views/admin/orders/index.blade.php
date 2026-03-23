<x-layouts.app :title="'Admin Orders'">
    <div class="grid">
        <div class="panel">
            <h1>Admin Orders</h1>
            <form method="get" action="{{ route('admin.orders.index') }}" class="grid" style="grid-template-columns:2fr 1fr auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Order code / customer target">
                </div>
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">Semua</option>
                        @foreach (['PENDING', 'PAID', 'PROCESSING', 'SUCCESS', 'FAILED'] as $status)
                            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="btn" type="submit">Filter</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Order Code</th>
                    <th>Status</th>
                    <th>Product</th>
                    <th>Target</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th>Created</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->order_code }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ $order->product?->name }}</td>
                        <td>{{ $order->customer_target ?: '-' }}</td>
                        <td>{{ $order->payment?->status ?? '-' }}</td>
                        <td>Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</td>
                        <td>{{ $order->created_at }}</td>
                        <td>
                            <a class="pill" href="{{ route('admin.orders.show', ['orderCode' => $order->order_code]) }}">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted">Data order kosong.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
