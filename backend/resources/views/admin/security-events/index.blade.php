<x-layouts.app :title="'Admin Security Events'">
    <div class="grid">
        <div class="panel">
            <h1>Security Events</h1>
            <p class="muted">Monitor event keamanan OTP/login dan aktivitas berisiko dengan filter severity, waktu, IP, dan user.</p>

            <form method="get" action="{{ route('admin.security-events.index') }}" class="grid" style="grid-template-columns:repeat(6, minmax(0, 1fr)); margin-top:12px;">
                <div>
                    <label for="event_code">Event Code</label>
                    <select id="event_code" name="event_code">
                        <option value="">Semua</option>
                        @foreach ($eventCodeOptions as $option)
                            <option value="{{ $option }}" @selected($filters['event_code'] === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="severity">Severity</label>
                    <select id="severity" name="severity">
                        <option value="">Semua</option>
                        @foreach ($severityOptions as $option)
                            <option value="{{ $option }}" @selected($filters['severity'] === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="ip">IP</label>
                    <input id="ip" name="ip" type="text" value="{{ $filters['ip'] }}" placeholder="172.16.x.x">
                </div>
                <div>
                    <label for="date_from">Date From</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}">
                </div>
                <div>
                    <label for="date_to">Date To</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}">
                </div>
                <div>
                    <label for="q">Search</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="event, user, context, user-agent">
                </div>
                <div style="grid-column:1/-1; display:flex; justify-content:flex-end; gap:10px;">
                    <a class="pill" href="{{ route('admin.security-events.export.csv', request()->query()) }}">Download CSV</a>
                    <a class="pill" href="{{ route('admin.security-events.index') }}">Reset</a>
                    <button class="btn" type="submit">Apply Filter</button>
                </div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Occurred At</th>
                    <th>Event</th>
                    <th>User</th>
                    <th>Risk</th>
                    <th>IP</th>
                    <th>Context</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($events as $event)
                    <tr>
                        <td>{{ $event->occurred_at }}</td>
                        <td>
                            <div><strong>{{ $event->event_code }}</strong></div>
                            @php
                                $severity = strtoupper((string) $event->severity);
                                $severityClass = $severity === 'HIGH' ? 'tag-fail' : ($severity === 'MEDIUM' ? 'tag-warn' : 'tag-pass');
                            @endphp
                            <span class="tag {{ $severityClass }}">{{ $severity }}</span>
                        </td>
                        <td>
                            @if ($event->user)
                                {{ $event->user->name }}<br>
                                <span class="muted">{{ $event->user->email }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ (int) $event->risk_score }}</td>
                        <td>
                            {{ $event->ip_address ?: '-' }}<br>
                            <span class="muted" style="font-size:11px;">{{ \Illuminate\Support\Str::limit((string) ($event->user_agent ?? '-'), 55) }}</span>
                        </td>
                        <td>
                            <details>
                                <summary>Lihat</summary>
                                <pre style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($event->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Tidak ada security event sesuai filter.</td></tr>
                @endforelse
                </tbody>
            </table>

            <div style="margin-top:12px;">
                {{ $events->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
