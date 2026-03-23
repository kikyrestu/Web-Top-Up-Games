<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $eventType = trim((string) $request->query('event_type', ''));
        $entityType = trim((string) $request->query('entity_type', ''));
        $actorType = trim((string) $request->query('actor_type', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        $search = trim((string) $request->query('q', ''));

        $logs = AuditLog::query()
            ->when($eventType !== '', static fn ($query) => $query->where('event_type', $eventType))
            ->when($entityType !== '', static fn ($query) => $query->where('entity_type', $entityType))
            ->when($actorType !== '', static fn ($query) => $query->where('actor_type', $actorType))
            ->when($dateFrom !== '', static fn ($query) => $query->whereDate('occurred_at', '>=', $dateFrom))
            ->when($dateTo !== '', static fn ($query) => $query->whereDate('occurred_at', '<=', $dateTo))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('event_type', 'like', '%'.$search.'%')
                        ->orWhere('request_id', 'like', '%'.$search.'%')
                        ->orWhere('ip_address', 'like', '%'.$search.'%')
                        ->orWhereRaw("CAST(payload AS TEXT) LIKE ?", ['%'.$search.'%']);
                });
            })
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
            'filters' => [
                'event_type' => $eventType,
                'entity_type' => $entityType,
                'actor_type' => $actorType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'q' => $search,
            ],
            'eventTypeOptions' => $eventTypeOptions,
            'entityTypeOptions' => $entityTypeOptions,
            'actorTypeOptions' => $actorTypeOptions,
        ]);
    }
}
