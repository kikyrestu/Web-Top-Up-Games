<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminSecurityEventController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);

        $events = $this->filteredQuery($filters)
            ->with(['user:id,name,email'])
            ->orderByDesc('occurred_at')
            ->paginate(30)
            ->withQueryString();

        $eventCodeOptions = SecurityEvent::query()
            ->select('event_code')
            ->distinct()
            ->orderBy('event_code')
            ->pluck('event_code');

        $severityOptions = SecurityEvent::query()
            ->select('severity')
            ->distinct()
            ->orderBy('severity')
            ->pluck('severity');

        return view('admin.security-events.index', [
            'events' => $events,
            'filters' => $filters,
            'eventCodeOptions' => $eventCodeOptions,
            'severityOptions' => $severityOptions,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->extractFilters($request);

        $events = $this->filteredQuery($filters)
            ->with(['user:id,name,email'])
            ->orderByDesc('occurred_at')
            ->limit(5000)
            ->get();

        $fileName = 'security-events-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(static function () use ($events): void {
            $stream = fopen('php://output', 'wb');
            if ($stream === false) {
                return;
            }

            fputcsv($stream, [
                'occurred_at',
                'event_code',
                'severity',
                'risk_score',
                'user_id',
                'user_name',
                'user_email',
                'ip_address',
                'user_agent',
                'context_json',
            ]);

            foreach ($events as $event) {
                fputcsv($stream, [
                    $event->occurred_at?->toISOString(),
                    (string) $event->event_code,
                    (string) $event->severity,
                    (string) $event->risk_score,
                    (string) ($event->user_id ?? ''),
                    (string) ($event->user?->name ?? ''),
                    (string) ($event->user?->email ?? ''),
                    (string) ($event->ip_address ?? ''),
                    (string) ($event->user_agent ?? ''),
                    json_encode($event->context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }

            fclose($stream);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function extractFilters(Request $request): array
    {
        return [
            'event_code' => trim((string) $request->query('event_code', '')),
            'severity' => strtoupper(trim((string) $request->query('severity', ''))),
            'ip' => trim((string) $request->query('ip', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
            'q' => trim((string) $request->query('q', '')),
        ];
    }

    /**
     * @param array<string, string> $filters
     */
    private function filteredQuery(array $filters): Builder
    {
        return SecurityEvent::query()
            ->when($filters['event_code'] !== '', static fn ($query) => $query->where('event_code', $filters['event_code']))
            ->when($filters['severity'] !== '', static fn ($query) => $query->where('severity', $filters['severity']))
            ->when($filters['ip'] !== '', static fn ($query) => $query->where('ip_address', 'like', '%'.$filters['ip'].'%'))
            ->when($filters['date_from'] !== '', static fn ($query) => $query->whereDate('occurred_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', static fn ($query) => $query->whereDate('occurred_at', '<=', $filters['date_to']))
            ->when($filters['q'] !== '', static function ($query) use ($filters): void {
                $search = $filters['q'];
                $query->where(function ($inner) use ($search): void {
                    $inner->where('event_code', 'like', '%'.$search.'%')
                        ->orWhere('ip_address', 'like', '%'.$search.'%')
                        ->orWhere('user_agent', 'like', '%'.$search.'%')
                        ->orWhereRaw("CAST(context AS TEXT) LIKE ?", ['%'.$search.'%'])
                        ->orWhereHas('user', static function ($userQuery) use ($search): void {
                            $userQuery->where('name', 'like', '%'.$search.'%')
                                ->orWhere('email', 'like', '%'.$search.'%');
                        });
                });
            });
    }
}
