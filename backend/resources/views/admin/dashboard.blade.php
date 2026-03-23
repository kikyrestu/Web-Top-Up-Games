<x-layouts.app :title="'Admin Dashboard'">
    <div class="grid">
        <div class="panel">
            <h1>System Dashboard</h1>
            <p class="muted">Ringkasan operasional 24 jam terakhir.</p>

            <div class="cards" style="margin-top:14px;">
                <div class="card"><div class="k">Pending Orders</div><div class="v">{{ $overview['orders_pending'] }}</div></div>
                <div class="card"><div class="k">Processing Orders</div><div class="v">{{ $overview['orders_processing'] }}</div></div>
                <div class="card"><div class="k">Failed Orders</div><div class="v">{{ $overview['orders_failed'] }}</div></div>
                <div class="card"><div class="k">Unpaid Payments</div><div class="v">{{ $overview['payments_unpaid'] }}</div></div>
                <div class="card"><div class="k">Active Providers</div><div class="v">{{ $overview['providers_active'] }}</div></div>
                <div class="card"><div class="k">Total Providers</div><div class="v">{{ $overview['providers_total'] }}</div></div>
            </div>
        </div>

        <div class="panel">
            <h2>Readiness Checks</h2>
            <p class="muted">Readiness score: <strong>{{ $readiness['score'] }}%</strong></p>
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin:10px 0 14px;">
                <span class="tag tag-pass">PASS {{ $readiness['summary']['pass'] }}</span>
                <span class="tag tag-warn">WARN {{ $readiness['summary']['warn'] }}</span>
                <span class="tag tag-fail">FAIL {{ $readiness['summary']['fail'] }}</span>
            </div>
            <table>
                <thead>
                <tr><th>Check</th><th>Status</th><th>Message</th></tr>
                </thead>
                <tbody>
                @foreach ($readiness['checks'] as $check)
                    <tr>
                        <td>{{ $check['code'] }}</td>
                        <td>{{ $check['status'] }}</td>
                        <td>{{ $check['message'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Provider Performance (24h)</h2>
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
                @forelse ($providerPerformance as $row)
                    <tr>
                        <td>{{ $row['provider_code'] }} - {{ $row['provider_name'] }}</td>
                        <td>{{ $row['attempts'] }}</td>
                        <td>{{ $row['success'] }}</td>
                        <td>{{ $row['failed'] }}</td>
                        <td>{{ $row['success_rate'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada data attempt.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Payment Performance (24h)</h2>
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
                @forelse ($paymentPerformance as $row)
                    <tr>
                        <td>{{ $row['gateway'] }}</td>
                        <td>{{ $row['total'] }}</td>
                        <td>{{ $row['paid'] }}</td>
                        <td>{{ $row['unpaid'] }}</td>
                        <td>{{ $row['failed'] }}</td>
                        <td>{{ $row['paid_rate'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada data payment.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Housekeeping Terakhir</h2>
            <table>
                <thead>
                <tr><th>Waktu</th><th>Deleted Records</th></tr>
                </thead>
                <tbody>
                @forelse ($housekeepingLogs as $log)
                    <tr>
                        <td>{{ $log->occurred_at }}</td>
                        <td>{{ (int) ((is_array($log->payload) ? ($log->payload['deleted_records'] ?? 0) : 0)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="muted">Belum ada riwayat purge.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
