<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SecurityEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_code' => ['nullable', 'string', 'max:80'],
            'severity' => ['nullable', 'string', 'max:20'],
            'ip' => ['nullable', 'string', 'max:64'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'q' => ['nullable', 'string', 'max:200'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $severity = strtoupper(trim((string) ($validated['severity'] ?? '')));
        $eventCode = trim((string) ($validated['event_code'] ?? ''));
        $ip = trim((string) ($validated['ip'] ?? ''));
        $search = trim((string) ($validated['q'] ?? ''));
        $dateFrom = trim((string) ($validated['date_from'] ?? ''));
        $dateTo = trim((string) ($validated['date_to'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 30);

        $events = SecurityEvent::query()
            ->with(['user:id,name,email'])
            ->when($eventCode !== '', static fn ($query) => $query->where('event_code', $eventCode))
            ->when($severity !== '', static fn ($query) => $query->where('severity', $severity))
            ->when($ip !== '', static fn ($query) => $query->where('ip_address', 'like', '%'.$ip.'%'))
            ->when($dateFrom !== '', static fn ($query) => $query->whereDate('occurred_at', '>=', $dateFrom))
            ->when($dateTo !== '', static fn ($query) => $query->whereDate('occurred_at', '<=', $dateTo))
            ->when($search !== '', static function ($query) use ($search): void {
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
            })
            ->orderByDesc('occurred_at')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_SECURITY_EVENTS_LIST',
            'message' => 'Security events loaded',
            'data' => [
                'items' => $events->getCollection()->map(static fn (SecurityEvent $event): array => [
                    'id' => (int) $event->id,
                    'event_code' => (string) $event->event_code,
                    'severity' => (string) $event->severity,
                    'risk_score' => (int) $event->risk_score,
                    'ip_address' => (string) ($event->ip_address ?? ''),
                    'user_agent' => (string) ($event->user_agent ?? ''),
                    'occurred_at' => $event->occurred_at?->toISOString(),
                    'user' => $event->user ? [
                        'id' => (int) $event->user->id,
                        'name' => (string) $event->user->name,
                        'email' => (string) $event->user->email,
                    ] : null,
                    'context' => is_array($event->context) ? $event->context : [],
                ])->values(),
                'meta' => [
                    'current_page' => $events->currentPage(),
                    'per_page' => $events->perPage(),
                    'last_page' => $events->lastPage(),
                    'total' => $events->total(),
                ],
            ],
        ]);
    }
}
