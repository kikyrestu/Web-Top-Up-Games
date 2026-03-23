<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);

        $logs = $this->filteredQuery($filters)
            ->orderByDesc('occurred_at')
            ->paginate(30)
            ->withQueryString();

        $eventTypeOptions = AuditLog::query()
            ->select('event_type')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        $entityTypeOptions = AuditLog::query()
            ->whereNotNull('entity_type')
            ->select('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');

        $actorTypeOptions = AuditLog::query()
            ->whereNotNull('actor_type')
            ->select('actor_type')
            ->distinct()
            ->orderBy('actor_type')
            ->pluck('actor_type');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'eventTypeOptions' => $eventTypeOptions,
            'entityTypeOptions' => $entityTypeOptions,
            'actorTypeOptions' => $actorTypeOptions,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->extractFilters($request);

        $logs = $this->filteredQuery($filters)
            ->orderByDesc('occurred_at')
            ->limit(5000)
            ->get();

        $fileName = 'audit-logs-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(static function () use ($logs): void {
            $stream = fopen('php://output', 'wb');
            if ($stream === false) {
                return;
            }

            fputcsv($stream, [
                'occurred_at',
                'event_type',
                'actor_type',
                'actor_id',
                'entity_type',
                'entity_id',
                'request_id',
                'ip_address',
                'payload_json',
            ]);

            foreach ($logs as $log) {
                fputcsv($stream, [
                    $log->occurred_at?->toISOString(),
                    (string) $log->event_type,
                    (string) ($log->actor_type ?? ''),
                    (string) ($log->actor_id ?? ''),
                    (string) ($log->entity_type ?? ''),
                    (string) ($log->entity_id ?? ''),
                    (string) ($log->request_id ?? ''),
                    (string) ($log->ip_address ?? ''),
                    json_encode($log->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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
            'event_type' => trim((string) $request->query('event_type', '')),
            'entity_type' => trim((string) $request->query('entity_type', '')),
            'actor_type' => trim((string) $request->query('actor_type', '')),
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
        return AuditLog::query()
            ->when($filters['event_type'] !== '', static fn ($query) => $query->where('event_type', $filters['event_type']))
            ->when($filters['entity_type'] !== '', static fn ($query) => $query->where('entity_type', $filters['entity_type']))
            ->when($filters['actor_type'] !== '', static fn ($query) => $query->where('actor_type', $filters['actor_type']))
            ->when($filters['date_from'] !== '', static fn ($query) => $query->whereDate('occurred_at', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', static fn ($query) => $query->whereDate('occurred_at', '<=', $filters['date_to']))
            ->when($filters['q'] !== '', static function ($query) use ($filters): void {
                $search = $filters['q'];
                $query->where(function ($inner) use ($search): void {
                    $inner->where('event_type', 'like', '%'.$search.'%')
                        ->orWhere('request_id', 'like', '%'.$search.'%')
                        ->orWhere('ip_address', 'like', '%'.$search.'%')
                        ->orWhereRaw("CAST(payload AS TEXT) LIKE ?", ['%'.$search.'%']);
                });
            });
    }
}
