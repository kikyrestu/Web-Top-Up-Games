<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Audit\Services\AuditLogService;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

final class AdminCustomerController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = strtoupper(trim((string) $request->query('status', '')));
        $segment = strtoupper(trim((string) $request->query('segment', '')));

        $rows = User::query()
            ->where('role', 'user')
            ->withCount('orders')
            ->withSum('orders', 'final_amount')
            ->when(in_array($status, ['ACTIVE', 'SUSPENDED'], true), static fn ($query) => $query->where('account_status', $status))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhere('phone_number', 'like', '%'.$search.'%');
                });
            })
            ->when(in_array($segment, ['NEW', 'ACTIVE', 'LOYAL', 'HIGH_SPENDER'], true), static function ($query) use ($segment): void {
                if ($segment === 'NEW') {
                    $query->has('orders', '=', 0);
                    return;
                }

                if ($segment === 'ACTIVE') {
                    $query->has('orders', '>=', 1)->has('orders', '<=', 4);
                    return;
                }

                if ($segment === 'LOYAL') {
                    $query->has('orders', '>=', 5);
                    return;
                }

                $query->whereHas('orders', static fn ($orderQuery) => $orderQuery->select(DB::raw('1'))->groupBy('user_id')->havingRaw('SUM(final_amount) >= 1000000'));
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', [
            'rows' => $rows,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'segment' => $segment,
            ],
        ]);
    }

    public function show(User $user): View
    {
        abort_if((string) ($user->role ?? 'user') !== 'user', 404);

        $summary = [
            'orders_total' => Order::query()->where('user_id', $user->id)->count(),
            'orders_success' => Order::query()->where('user_id', $user->id)->where('status', 'SUCCESS')->count(),
            'orders_failed' => Order::query()->where('user_id', $user->id)->where('status', 'FAILED')->count(),
            'spend_total' => (float) (Order::query()->where('user_id', $user->id)->sum('final_amount') ?: 0),
            'reviews_total' => Review::query()->where('user_id', $user->id)->count(),
        ];

        $recentOrders = Order::query()
            ->with(['product:id,name,type', 'payment:id,order_id,status,gateway'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentReviews = Review::query()
            ->with('product:id,name,slug')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.customers.show', [
            'row' => $user,
            'summary' => $summary,
            'recentOrders' => $recentOrders,
            'recentReviews' => $recentReviews,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if((string) ($user->role ?? 'user') !== 'user', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[A-Za-z0-9_.-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'phone_number' => ['nullable', 'string', 'max:25', 'regex:/^[0-9]{10,16}$/'],
            'account_status' => ['required', Rule::in(['ACTIVE', 'SUSPENDED'])],
            'password' => ['nullable', 'string', 'min:8', 'max:120'],
            'note' => ['nullable', 'string', 'max:2000'],
            'revoke_sessions' => ['nullable', 'boolean'],
        ]);

        $previous = [
            'name' => (string) ($user->name ?? ''),
            'email' => (string) ($user->email ?? ''),
            'username' => (string) ($user->username ?? ''),
            'phone_number' => (string) ($user->phone_number ?? ''),
            'account_status' => strtoupper((string) ($user->account_status ?? 'ACTIVE')),
        ];

        $user->update([
            'name' => trim((string) $validated['name']),
            'email' => strtolower(trim((string) $validated['email'])),
            'username' => strtolower(trim((string) $validated['username'])),
            'phone_number' => isset($validated['phone_number']) ? trim((string) $validated['phone_number']) : null,
            'account_status' => strtoupper((string) $validated['account_status']),
            'password' => trim((string) ($validated['password'] ?? '')) !== '' ? Hash::make((string) $validated['password']) : (string) $user->password,
        ]);

        $revokeSessions = $request->boolean('revoke_sessions') || strtoupper((string) $validated['account_status']) === 'SUSPENDED';
        if ($revokeSessions) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $this->auditLogService->write([
            'event_type' => 'CUSTOMER_PROFILE_UPDATED_WEB',
            'actor_type' => 'USER',
            'actor_id' => auth()->id(),
            'entity_type' => 'USER',
            'entity_id' => $user->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'note' => trim((string) ($validated['note'] ?? '')),
                'previous' => $previous,
                'updated' => [
                    'name' => (string) $user->name,
                    'email' => (string) $user->email,
                    'username' => (string) $user->username,
                    'phone_number' => (string) ($user->phone_number ?? ''),
                    'account_status' => strtoupper((string) ($user->account_status ?? 'ACTIVE')),
                ],
                'password_changed' => trim((string) ($validated['password'] ?? '')) !== '',
                'sessions_revoked' => $revokeSessions,
            ],
            'occurred_at' => now(),
        ]);

        return redirect()->route('admin.customers.show', ['user' => $user->id])->with('notice', 'Profil customer berhasil diperbarui.');
    }
}
