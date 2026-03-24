<x-layouts.app :title="'Advanced Analytics'">
    @php
        $overview = is_array($overview ?? null) ? $overview : [];
        $funnel = is_array($funnel ?? null) ? $funnel : [];
        $timeSeries = is_iterable($timeSeries ?? null) ? collect($timeSeries) : collect();
        $gatewayPerformance = is_iterable($gatewayPerformance ?? null) ? collect($gatewayPerformance) : collect();
        $productPerformance = is_iterable($productPerformance ?? null) ? collect($productPerformance) : collect();
        $cohortRows = is_iterable($cohortRows ?? null) ? collect($cohortRows) : collect();
        $windowDays = (int) ($windowDays ?? 30);
    @endphp

    <div class="grid">
        <div class="panel">
            <h1>Advanced Analytics Dashboard</h1>
            <p class="muted">Funnel, cohort, dan conversion deep-dive untuk {{ $windowDays }} hari terakhir.</p>

            <form method="get" action="{{ route('admin.analytics.index') }}" class="grid" style="grid-template-columns:160px auto; align-items:end; margin:12px 0 6px;">
                <div>
                    <label for="days">Window</label>
                    <select id="days" name="days">
                        @foreach ([7, 14, 30, 60, 90] as $dayOption)
                            <option value="{{ $dayOption }}" @selected($windowDays === $dayOption)>{{ $dayOption }} hari</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="btn" type="submit">Apply</button>
                </div>
            </form>

            <div class="cards" style="margin-top:14px;">
                <div class="card"><div class="k">Total Orders</div><div class="v">{{ (int) ($overview['total_orders'] ?? 0) }}</div></div>
                <div class="card"><div class="k">Successful Orders</div><div class="v">{{ (int) ($overview['successful_orders'] ?? 0) }}</div></div>
                <div class="card"><div class="k">Failed Orders</div><div class="v">{{ (int) ($overview['failed_orders'] ?? 0) }}</div></div>
                <div class="card"><div class="k">Gross Revenue</div><div class="v">Rp {{ number_format((float) ($overview['gross_revenue'] ?? 0), 0, ',', '.') }}</div></div>
                <div class="card"><div class="k">Net Revenue</div><div class="v">Rp {{ number_format((float) ($overview['net_revenue'] ?? 0), 0, ',', '.') }}</div></div>
                <div class="card"><div class="k">Refunded Amount</div><div class="v">Rp {{ number_format((float) ($overview['refunded_amount'] ?? 0), 0, ',', '.') }}</div></div>
            </div>
        </div>

        <div class="panel">
            <h2>Funnel Snapshot</h2>
            <table>
                <thead>
                <tr><th>Stage</th><th>Volume</th><th>Conversion</th></tr>
                </thead>
                <tbody>
                <tr>
                    <td>Checkout Initiated</td>
                    <td>{{ (int) ($funnel['checkout_initiated'] ?? 0) }}</td>
                    <td>100%</td>
                </tr>
                <tr>
                    <td>Payment Confirmed</td>
                    <td>{{ (int) ($funnel['payment_confirmed'] ?? 0) }}</td>
                    <td>{{ (float) ($funnel['payment_conversion_pct'] ?? 0) }}%</td>
                </tr>
                <tr>
                    <td>Fulfillment Success</td>
                    <td>{{ (int) ($funnel['fulfillment_success'] ?? 0) }}</td>
                    <td>{{ (float) ($funnel['success_conversion_pct'] ?? 0) }}%</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Daily Trend</h2>
            <table>
                <thead>
                <tr><th>Day</th><th>Orders</th><th>Success</th><th>Failed</th><th>Success Rate</th><th>GMV</th></tr>
                </thead>
                <tbody>
                @forelse ($timeSeries as $row)
                    <tr>
                        <td>{{ $row['day'] ?? '-' }}</td>
                        <td>{{ (int) ($row['orders_total'] ?? 0) }}</td>
                        <td>{{ (int) ($row['orders_success'] ?? 0) }}</td>
                        <td>{{ (int) ($row['orders_failed'] ?? 0) }}</td>
                        <td>{{ (float) ($row['success_rate_pct'] ?? 0) }}%</td>
                        <td>Rp {{ number_format((float) ($row['gmv'] ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada data harian.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Gateway Conversion Deep-Dive</h2>
            <table>
                <thead>
                <tr><th>Gateway</th><th>Total</th><th>Paid</th><th>Refunded</th><th>Unpaid</th><th>Failed</th><th>Paid Rate</th></tr>
                </thead>
                <tbody>
                @forelse ($gatewayPerformance as $row)
                    <tr>
                        <td>{{ $row['gateway'] ?? '-' }}</td>
                        <td>{{ (int) ($row['total'] ?? 0) }}</td>
                        <td>{{ (int) ($row['paid_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['refunded_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['unpaid_count'] ?? 0) }}</td>
                        <td>{{ (int) ($row['failed_count'] ?? 0) }}</td>
                        <td>{{ (float) ($row['paid_rate_pct'] ?? 0) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Belum ada data gateway.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Product Conversion Deep-Dive</h2>
            <table>
                <thead>
                <tr><th>Product</th><th>Type</th><th>Orders</th><th>Success</th><th>Failed</th><th>Refunded</th><th>Success Rate</th><th>GMV</th></tr>
                </thead>
                <tbody>
                @forelse ($productPerformance as $row)
                    <tr>
                        <td>{{ $row['product_name'] ?? '-' }}</td>
                        <td>{{ $row['product_type'] ?? '-' }}</td>
                        <td>{{ (int) ($row['orders_total'] ?? 0) }}</td>
                        <td>{{ (int) ($row['orders_success'] ?? 0) }}</td>
                        <td>{{ (int) ($row['orders_failed'] ?? 0) }}</td>
                        <td>{{ (int) ($row['orders_refunded'] ?? 0) }}</td>
                        <td>{{ (float) ($row['success_rate_pct'] ?? 0) }}%</td>
                        <td>Rp {{ number_format((float) ($row['gmv'] ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="muted">Belum ada data produk.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Weekly Cohort Retention (30D)</h2>
            <table>
                <thead>
                <tr><th>Cohort Week</th><th>New Customers</th><th>Repeat in 30D</th><th>Retention 30D</th></tr>
                </thead>
                <tbody>
                @forelse ($cohortRows as $row)
                    <tr>
                        <td>{{ $row['cohort_week'] ?? '-' }}</td>
                        <td>{{ (int) ($row['new_customers'] ?? 0) }}</td>
                        <td>{{ (int) ($row['repeat_30d'] ?? 0) }}</td>
                        <td>{{ (float) ($row['retention_30d_pct'] ?? 0) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Belum ada data cohort pada window ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
