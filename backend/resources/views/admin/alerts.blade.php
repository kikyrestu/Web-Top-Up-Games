<x-layouts.app :title="'Admin Alerts'">
    @php
        $alertsContainer = is_array($alerts['alerts'] ?? null) ? $alerts['alerts'] : [];
        $providerAlerts = is_iterable($alertsContainer['providers'] ?? null) ? collect($alertsContainer['providers']) : collect();
        $paymentAlerts = is_iterable($alertsContainer['payments'] ?? null) ? collect($alertsContainer['payments']) : collect();
        $providerMetrics = is_iterable($metrics['providers'] ?? null) ? collect($metrics['providers']) : collect();
        $paymentMetrics = is_iterable($metrics['payments'] ?? null) ? collect($metrics['payments']) : collect();
    @endphp

    <div class="grid">
        <div class="panel">
            <h1>Alert Center</h1>
            <p class="muted">Filter threshold alert provider dan payment, lalu pantau hasilnya.</p>

            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:10px;">
                <a class="pill" href="{{ route('admin.dashboard.alerts', array_merge(request()->query(), ['severity' => 'ALL'])) }}">Severity: ALL</a>
                <a class="pill" href="{{ route('admin.dashboard.alerts', array_merge(request()->query(), ['severity' => 'HIGH'])) }}">Severity: HIGH</a>
                <a class="pill" href="{{ route('admin.dashboard.alerts', array_merge(request()->query(), ['severity' => 'MEDIUM'])) }}">Severity: MEDIUM</a>
            </div>

            <form method="get" action="{{ route('admin.dashboard.alerts') }}" class="grid" style="grid-template-columns:repeat(6, minmax(0, 1fr)); margin-top:12px;">
                <div>
                    <label for="hours">Window Hours</label>
                    <input id="hours" name="hours" type="number" min="1" max="168" value="{{ $filters['hours'] }}">
                </div>
                <div>
                    <label for="severity">Severity</label>
                    <select id="severity" name="severity">
                        <option value="ALL" @selected($filters['severity'] === 'ALL')>ALL</option>
                        <option value="HIGH" @selected($filters['severity'] === 'HIGH')>HIGH</option>
                        <option value="MEDIUM" @selected($filters['severity'] === 'MEDIUM')>MEDIUM</option>
                    </select>
                </div>
                <div>
                    <label for="alert_success_rate_threshold">Provider Success Threshold (%)</label>
                    <input id="alert_success_rate_threshold" name="alert_success_rate_threshold" type="number" min="1" max="100" step="0.1" value="{{ $filters['alert_success_rate_threshold'] }}">
                </div>
                <div>
                    <label for="alert_min_attempts">Provider Min Attempts</label>
                    <input id="alert_min_attempts" name="alert_min_attempts" type="number" min="1" max="500" value="{{ $filters['alert_min_attempts'] }}">
                </div>
                <div>
                    <label for="payment_alert_paid_rate_threshold">Payment Paid Threshold (%)</label>
                    <input id="payment_alert_paid_rate_threshold" name="payment_alert_paid_rate_threshold" type="number" min="1" max="100" step="0.1" value="{{ $filters['payment_alert_paid_rate_threshold'] }}">
                </div>
                <div>
                    <label for="payment_alert_min_total">Payment Min Total</label>
                    <input id="payment_alert_min_total" name="payment_alert_min_total" type="number" min="1" max="500" value="{{ $filters['payment_alert_min_total'] }}">
                </div>
                <div style="grid-column:1/-1; display:flex; gap:10px; justify-content:flex-end;">
                    <button class="btn" type="submit">Apply Filter</button>
                    <a class="pill" href="{{ route('admin.dashboard.metrics.excel', request()->query()) }}">Download Metrics Excel</a>
                </div>
            </form>
        </div>

        <div class="panel">
            <h2>Provider Alerts</h2>
            <table>
                <thead>
                <tr><th>Provider</th><th>Attempts</th><th>Success Rate</th><th>Threshold</th><th>Severity</th></tr>
                </thead>
                <tbody>
                @forelse ($providerAlerts as $alert)
                    <tr>
                        <td>{{ $alert['provider_code'] ?? '-' }} - {{ $alert['provider_name'] ?? '-' }}</td>
                        <td>{{ (int) ($alert['attempts'] ?? 0) }}</td>
                        <td>{{ (float) ($alert['success_rate_pct'] ?? 0) }}%</td>
                        <td>{{ (float) ($alert['threshold_pct'] ?? 0) }}%</td>
                        <td>
                            @php $providerSeverity = strtoupper((string) ($alert['severity'] ?? '-')); @endphp
                            <span class="tag {{ $providerSeverity === 'HIGH' ? 'tag-fail' : 'tag-warn' }}">{{ $providerSeverity }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Tidak ada provider alert.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Payment Alerts</h2>
            <table>
                <thead>
                <tr><th>Gateway</th><th>Total</th><th>Paid Rate</th><th>Threshold</th><th>Severity</th></tr>
                </thead>
                <tbody>
                @forelse ($paymentAlerts as $alert)
                    <tr>
                        <td>{{ $alert['gateway'] ?? '-' }}</td>
                        <td>{{ (int) ($alert['total'] ?? 0) }}</td>
                        <td>{{ (float) ($alert['paid_rate_pct'] ?? 0) }}%</td>
                        <td>{{ (float) ($alert['threshold_pct'] ?? 0) }}%</td>
                        <td>
                            @php $paymentSeverity = strtoupper((string) ($alert['severity'] ?? '-')); @endphp
                            <span class="tag {{ $paymentSeverity === 'HIGH' ? 'tag-fail' : 'tag-warn' }}">{{ $paymentSeverity }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Tidak ada payment alert.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Provider Metrics Snapshot</h2>
            <table>
                <thead>
                <tr><th>Provider</th><th>Attempts</th><th>Success</th><th>Failed</th><th>P95 Latency</th></tr>
                </thead>
                <tbody>
                @forelse ($providerMetrics as $row)
                    <tr>
                        <td>{{ $row['provider_code'] ?? '-' }}</td>
                        <td>{{ (int) ($row['attempts'] ?? 0) }}</td>
                        <td>{{ (int) ($row['success_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['failed_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['p95_latency_ms'] ?? 0) }} ms</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada metrics provider.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Payment Metrics Snapshot</h2>
            <table>
                <thead>
                <tr><th>Gateway</th><th>Total</th><th>Paid</th><th>Unpaid</th><th>Failed</th></tr>
                </thead>
                <tbody>
                @forelse ($paymentMetrics as $row)
                    <tr>
                        <td>{{ $row['gateway'] ?? '-' }}</td>
                        <td>{{ (int) ($row['total'] ?? 0) }}</td>
                        <td>{{ (int) ($row['paid_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['unpaid_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['failed_count'] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">Belum ada metrics payment.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
