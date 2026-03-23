<x-layouts.app :title="'Admin Audit Logs'">
    <div class="grid">
        <div class="panel">
            <h1>Audit Logs</h1>
            <p class="muted">Jejak aktivitas sistem dan admin dengan filter event, actor, entity, dan waktu.</p>

            <form method="get" action="{{ route('admin.audit-logs.index') }}" class="grid" style="grid-template-columns:repeat(6, minmax(0, 1fr)); margin-top:12px;">
                <div>
                    <label for="event_type">Event Type</label>
                    <select id="event_type" name="event_type">
                        <option value="">Semua</option>
                        @foreach ($eventTypeOptions as $option)
                            <option value="{{ $option }}" @selected($filters['event_type'] === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="actor_type">Actor Type</label>
                    <select id="actor_type" name="actor_type">
                        <option value="">Semua</option>
                        @foreach ($actorTypeOptions as $option)
                            <option value="{{ $option }}" @selected($filters['actor_type'] === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="entity_type">Entity Type</label>
                    <select id="entity_type" name="entity_type">
                        <option value="">Semua</option>
                        @foreach ($entityTypeOptions as $option)
                            <option value="{{ $option }}" @selected($filters['entity_type'] === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
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
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="event, request id, ip, payload">
                </div>
                <div style="grid-column:1/-1; display:flex; justify-content:flex-end; gap:10px;">
                    <a class="pill" href="{{ route('admin.audit-logs.index') }}">Reset</a>
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
                    <th>Actor</th>
                    <th>Entity</th>
                    <th>IP</th>
                    <th>Payload</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->occurred_at }}</td>
                        <td>
                            <div><strong>{{ $log->event_type }}</strong></div>
                            <div class="muted">request: {{ $log->request_id ?: '-' }}</div>
                        </td>
                        <td>{{ $log->actor_type ?: '-' }} {{ $log->actor_id ? '#'.$log->actor_id : '' }}</td>
                        <td>{{ $log->entity_type ?: '-' }} {{ $log->entity_id ? '#'.$log->entity_id : '' }}</td>
                        <td>{{ $log->ip_address ?: '-' }}</td>
                        <td>
                            <details>
                                <summary>Lihat</summary>
                                <pre style="white-space: pre-wrap; word-break: break-word;">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Tidak ada audit log sesuai filter.</td></tr>
                @endforelse
                </tbody>
            </table>

            <div style="margin-top:12px;">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
