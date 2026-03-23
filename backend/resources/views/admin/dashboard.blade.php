<x-layouts.app :title="'Admin Dashboard'">
    @php
        $ordersOverview = is_array($overview['orders'] ?? null) ? $overview['orders'] : [];
        $paymentsOverview = is_array($overview['payments'] ?? null) ? $overview['payments'] : [];
        $providersOverview = is_iterable($overview['providers'] ?? null) ? collect($overview['providers']) : collect();
        $providerMetrics = is_iterable($metrics['providers'] ?? null) ? collect($metrics['providers']) : collect();
        $paymentMetrics = is_iterable($metrics['payments'] ?? null) ? collect($metrics['payments']) : collect();
        $alertsContainer = is_array($alerts['alerts'] ?? null) ? $alerts['alerts'] : [];
        $providerAlerts = is_iterable($alertsContainer['providers'] ?? null) ? collect($alertsContainer['providers']) : collect();
        $paymentAlerts = is_iterable($alertsContainer['payments'] ?? null) ? collect($alertsContainer['payments']) : collect();
        $housekeepingIdempotency = is_array($housekeeping['idempotency'] ?? null) ? $housekeeping['idempotency'] : [];
        $housekeepingRuns = is_iterable($housekeepingHistory['runs'] ?? null) ? collect($housekeepingHistory['runs']) : collect();
        $readinessSummary = is_array($readiness['summary'] ?? null) ? $readiness['summary'] : ['pass' => 0, 'warn' => 0, 'fail' => 0];
        $readinessChecks = is_iterable($readiness['checks'] ?? null) ? collect($readiness['checks']) : collect();
        $rateLimitContainer = is_array($rateLimitStats ?? null) ? $rateLimitStats : [];
        $rateLimitRows = is_iterable($rateLimitContainer['rows'] ?? null) ? collect($rateLimitContainer['rows']) : collect();
        $rateLimitTotals = is_iterable($rateLimitContainer['totals'] ?? null) ? collect($rateLimitContainer['totals']) : collect();
    @endphp

    <div class="grid">
        <div class="panel">
            <h1>System Dashboard</h1>
            <p class="muted">Ringkasan operasional 24 jam terakhir.</p>
            <div style="display:flex; gap:10px; margin-top:10px; margin-bottom:6px;">
                <a class="pill" href="{{ route('admin.dashboard.alerts') }}">Buka Alert Center</a>
                <a class="pill" href="{{ route('admin.dashboard.metrics.excel') }}">Download Metrics Excel</a>
            </div>

            <div class="cards" style="margin-top:14px;">
                <div class="card"><div class="k">Pending Orders</div><div class="v">{{ (int) ($ordersOverview['pending'] ?? 0) }}</div></div>
                <div class="card"><div class="k">Processing Orders</div><div class="v">{{ (int) ($ordersOverview['processing'] ?? 0) }}</div></div>
                <div class="card"><div class="k">Failed Orders</div><div class="v">{{ (int) ($ordersOverview['failed'] ?? 0) }}</div></div>
                <div class="card"><div class="k">Unpaid Payments</div><div class="v">{{ (int) ($paymentsOverview['unpaid'] ?? 0) }}</div></div>
                <div class="card"><div class="k">Active Providers</div><div class="v">{{ $providersOverview->where('is_active', true)->count() }}</div></div>
                <div class="card"><div class="k">Total Providers</div><div class="v">{{ $providersOverview->count() }}</div></div>
            </div>
        </div>

        <div class="panel">
            <h2>Readiness Checks</h2>
            <p class="muted">Readiness score: <strong>{{ (float) ($readiness['score'] ?? 0) }}%</strong></p>
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin:10px 0 14px;">
                <span class="tag tag-pass">PASS {{ (int) ($readinessSummary['pass'] ?? 0) }}</span>
                <span class="tag tag-warn">WARN {{ (int) ($readinessSummary['warn'] ?? 0) }}</span>
                <span class="tag tag-fail">FAIL {{ (int) ($readinessSummary['fail'] ?? 0) }}</span>
            </div>
            <table>
                <thead>
                <tr><th>Check</th><th>Status</th><th>Message</th></tr>
                </thead>
                <tbody>
                @foreach ($readinessChecks as $check)
                    <tr>
                        <td>{{ $check['code'] ?? '-' }}</td>
                        <td>{{ $check['status'] ?? '-' }}</td>
                        <td>{{ $check['message'] ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Provider Metrics (24h)</h2>
            <table>
                <thead>
                <tr>
                    <th>Provider</th>
                    <th>Attempts</th>
                    <th>Success</th>
                    <th>Failed</th>
                    <th>Success Rate</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($providerMetrics as $row)
                    <tr>
                        <td>{{ $row['provider_code'] ?? '-' }} - {{ $row['provider_name'] ?? '-' }}</td>
                        <td>{{ (int) ($row['attempts'] ?? 0) }}</td>
                        <td>{{ (int) ($row['success_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['failed_count'] ?? 0) }}</td>
                        <td>{{ (float) ($row['success_rate_pct'] ?? 0) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada data attempt.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Payment Metrics (24h)</h2>
            <table>
                <thead>
                <tr>
                    <th>Gateway</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Unpaid</th>
                    <th>Failed</th>
                    <th>Paid Rate</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($paymentMetrics as $row)
                    <tr>
                        <td>{{ $row['gateway'] ?? '-' }}</td>
                        <td>{{ (int) ($row['total'] ?? 0) }}</td>
                        <td>{{ (int) ($row['paid_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['unpaid_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['failed_count'] ?? 0) }}</td>
                        <td>{{ (float) ($row['paid_rate_pct'] ?? 0) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada data payment.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Alerts (24h)</h2>
            <table>
                <thead>
                <tr><th>Type</th><th>Target</th><th>Severity</th><th>Info</th></tr>
                </thead>
                <tbody>
                @forelse ($providerAlerts as $alert)
                    <tr>
                        <td>PROVIDER</td>
                        <td>{{ $alert['provider_code'] ?? '-' }}</td>
                        <td>{{ $alert['severity'] ?? '-' }}</td>
                        <td>Success {{ (float) ($alert['success_rate_pct'] ?? 0) }}% / {{ (float) ($alert['threshold_pct'] ?? 0) }}%</td>
                    </tr>
                @empty
                @endforelse
                @forelse ($paymentAlerts as $alert)
                    <tr>
                        <td>PAYMENT</td>
                        <td>{{ $alert['gateway'] ?? '-' }}</td>
                        <td>{{ $alert['severity'] ?? '-' }}</td>
                        <td>Paid {{ (float) ($alert['paid_rate_pct'] ?? 0) }}% / {{ (float) ($alert['threshold_pct'] ?? 0) }}%</td>
                    </tr>
                @empty
                @endforelse
                @if ($providerAlerts->isEmpty() && $paymentAlerts->isEmpty())
                    <tr><td colspan="4" class="muted">Tidak ada alert pada window ini.</td></tr>
                @endif
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Rate Limit Monitor</h2>
            <p class="muted">{{ $rateLimitContainer['window_label'] ?? 'Window' }}</p>

            <div class="cards" style="margin:10px 0 12px;">
                @foreach ($rateLimitTotals as $total)
                    <div class="card">
                        <div class="k">{{ strtoupper((string) ($total['profile'] ?? '-')) }}</div>
                        <div class="v" style="font-size:20px;">{{ (int) ($total['blocked'] ?? 0) }}/{{ (int) ($total['hits'] ?? 0) }}</div>
                        <div class="muted">Blocked rate {{ (float) ($total['blocked_rate_pct'] ?? 0) }}%</div>
                    </div>
                @endforeach
            </div>

            <table>
                <thead>
                <tr><th>Profile</th><th>Hour</th><th>Hits</th><th>Blocked</th><th>Blocked Rate</th></tr>
                </thead>
                <tbody>
                @forelse ($rateLimitRows as $row)
                    <tr>
                        <td>{{ $row['profile'] ?? '-' }}</td>
                        <td>{{ $row['hour'] ?? '-' }}</td>
                        <td>{{ (int) ($row['hits'] ?? 0) }}</td>
                        <td>{{ (int) ($row['blocked'] ?? 0) }}</td>
                        <td>{{ (float) ($row['blocked_rate_pct'] ?? 0) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada data rate limit.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Housekeeping</h2>
            <div class="cards" style="margin-bottom:14px;">
                <div class="card"><div class="k">Total Records</div><div class="v">{{ (int) ($housekeepingIdempotency['total_records'] ?? 0) }}</div></div>
                <div class="card"><div class="k">Expired Records</div><div class="v">{{ (int) ($housekeepingIdempotency['expired_records'] ?? 0) }}</div></div>
                <div class="card"><div class="k">Purge Deleted</div><div class="v">{{ (int) ($housekeepingIdempotency['purge_total_deleted'] ?? 0) }}</div></div>
            </div>

            <table>
                <thead>
                <tr><th>Run At</th><th>Deleted Records</th></tr>
                </thead>
                <tbody>
                @forelse ($housekeepingRuns as $run)
                    <tr>
                        <td>{{ $run['run_at'] ?? '-' }}</td>
                        <td>{{ (int) ($run['deleted_records'] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="muted">Belum ada riwayat purge.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Recent Orders</h2>
            <table>
                <thead>
                <tr><th>Order</th><th>Status</th><th>Product</th><th>Payment</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @forelse ($recentOrders as $order)
                    <tr>
                        <td>{{ $order->order_code }}</td>
                        <td>{{ $order->status }}</td>
                        <td>{{ $order->product?->name }}</td>
                        <td>{{ $order->payment?->status ?? '-' }}</td>
                        <td><a class="pill" href="{{ route('admin.orders.show', ['orderCode' => $order->order_code]) }}">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada order.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
