<x-layouts.app :title="'Admin Order ' . $order->order_code">
    @php
        $orderStatus = strtoupper((string) $order->status);
        $canReprocess = $orderStatus === 'FAILED';
        $canVoid = in_array($orderStatus, ['PENDING', 'PAID'], true);
        $canRefund = in_array($orderStatus, ['PAID', 'PROCESSING', 'SUCCESS', 'FAILED', 'DISPUTED'], true);
        $canDispute = in_array($orderStatus, ['PENDING', 'PAID', 'PROCESSING', 'SUCCESS', 'FAILED'], true);
    @endphp
    <div class="grid">
        <div class="panel">
            <h1>Order Detail</h1>
            <p class="muted">Kode: <strong>{{ $order->order_code }}</strong></p>
            <div style="margin-top:10px; margin-bottom:4px;">
                <a class="pill" href="{{ route('admin.audit-logs.index', ['entity_type' => 'ORDER', 'q' => $order->order_code]) }}">Lihat Audit Log Terkait</a>
            </div>

            <div class="cards" style="margin-top:12px;">
                <div class="card"><div class="k">Order Status</div><div class="v">{{ $order->status }}</div></div>
                <div class="card"><div class="k">Payment Status</div><div class="v">{{ $order->payment?->status ?? '-' }}</div></div>
                <div class="card"><div class="k">Final Amount</div><div class="v">Rp {{ number_format((float) $order->final_amount, 0, ',', '.') }}</div></div>
            </div>

            @if ($canReprocess)
                <form method="post" action="{{ route('admin.orders.reprocess', ['orderCode' => $order->order_code]) }}" style="margin-top:14px;">
                    @csrf
                    <button type="submit" class="btn">Reprocess Order</button>
                </form>
            @endif
        </div>

        <div class="panel">
            <h2>Manual Actions</h2>
            <p class="muted">Aksi operasional ini akan tercatat ke audit log dan operation log.</p>

            @if ($canVoid)
                <form method="post" action="{{ route('admin.orders.void', ['orderCode' => $order->order_code]) }}" class="grid" style="margin-top:10px;">
                    @csrf
                    <label for="void_note">Void Note</label>
                    <textarea id="void_note" name="note" rows="2" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;" placeholder="Alasan void order...">{{ old('note') }}</textarea>
                    <div><button type="submit" class="btn btn-ghost">Void Order</button></div>
                </form>
            @endif

            @if ($canRefund)
                <form method="post" action="{{ route('admin.orders.refund', ['orderCode' => $order->order_code]) }}" class="grid" style="margin-top:14px;">
                    @csrf
                    <label for="refund_note">Refund Note</label>
                    <textarea id="refund_note" name="note" rows="2" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;" placeholder="Alasan refund...">{{ old('note') }}</textarea>
                    <div>
                        <label for="refund_amount">Refund Amount (opsional)</label>
                        <input id="refund_amount" name="refund_amount" type="number" min="0" step="0.01" value="{{ old('refund_amount') }}" placeholder="Kosong = full refund">
                    </div>
                    <div><button type="submit" class="btn btn-ghost">Refund Order</button></div>
                </form>
            @endif

            @if ($canDispute)
                <form method="post" action="{{ route('admin.orders.dispute', ['orderCode' => $order->order_code]) }}" class="grid" style="margin-top:14px;">
                    @csrf
                    <label for="dispute_note">Dispute Note</label>
                    <textarea id="dispute_note" name="note" rows="2" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;" placeholder="Alasan dispute...">{{ old('note') }}</textarea>
                    <div><button type="submit" class="btn btn-ghost">Mark as Dispute</button></div>
                </form>
            @endif

            @if (!$canVoid && !$canRefund && !$canDispute)
                <p class="muted" style="margin-top:10px;">Tidak ada aksi manual yang tersedia untuk status order saat ini.</p>
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
            <form method="get" action="{{ route('admin.orders.show', ['orderCode' => $order->order_code]) }}" style="display:flex; gap:10px; margin-bottom:10px; align-items:end;">
                <div style="flex:1;">
                    <label for="payload_q">Cari di payload/status/ref</label>
                    <input id="payload_q" name="payload_q" type="text" value="{{ $payloadSearch }}" placeholder="contoh: timeout, provider_ref, error code">
                </div>
                <button class="btn" type="submit">Search</button>
                <a class="pill" href="{{ route('admin.orders.show', ['orderCode' => $order->order_code]) }}">Reset</a>
            </form>

            <table>
                <thead>
                <tr><th>No</th><th>Provider</th><th>Status</th><th>Ref</th><th>At</th><th>Payload</th></tr>
                </thead>
                <tbody>
                @forelse ($attempts as $attempt)
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

        <div class="panel">
            <h2>Operation Logs</h2>
            <table>
                <thead>
                <tr><th>At</th><th>Actor</th><th>Action</th><th>Status</th><th>Refund</th><th>Note</th></tr>
                </thead>
                <tbody>
                @forelse ($operationLogs as $log)
                    <tr>
                        <td>{{ $log->acted_at }}</td>
                        <td>{{ $log->actor?->name ?: 'system' }}</td>
                        <td>{{ $log->action_type }}</td>
                        <td>{{ $log->previous_status ?: '-' }} -> {{ $log->new_status ?: '-' }}</td>
                        <td>{{ $log->refund_amount !== null ? 'Rp '.number_format((float) $log->refund_amount, 0, ',', '.') : '-' }}</td>
                        <td>{{ $log->note ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada operation logs.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
