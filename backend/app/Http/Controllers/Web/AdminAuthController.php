<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Audit\Services\AuditLogService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

final class AdminAuthController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function showLogin(Request $request): View
    {
        if (!$this->hasAdminPanelQuery($request)) {
            abort(404);
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (!$this->hasAdminPanelQuery($request)) {
            abort(404);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $throttleKey = $this->throttleKey((string) $validated['email'], $request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->writeAudit('ADMIN_LOGIN_BLOCKED_WEB', $request, [
                'email' => (string) $validated['email'],
                'reason' => 'too_many_attempts',
                'retry_after_seconds' => $seconds,
            ]);

            return back()->withErrors([
                'email' => 'Terlalu banyak percobaan login admin. Coba lagi '.$seconds.' detik lagi.',
            ])->onlyInput('email');
        }

        if (!Auth::attempt($validated)) {
            RateLimiter::hit($throttleKey, 300);
            $this->writeAudit('ADMIN_LOGIN_FAILED_WEB', $request, [
                'email' => (string) $validated['email'],
                'reason' => 'invalid_credentials',
            ]);

            return back()->withErrors([
                'email' => 'Email atau password tidak valid.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ((string) (Auth::user()?->role ?? 'user') !== 'admin') {
            RateLimiter::hit($throttleKey, 300);
            $this->writeAudit('ADMIN_LOGIN_FAILED_WEB', $request, [
                'email' => (string) $validated['email'],
                'reason' => 'non_admin_role',
                'user_id' => Auth::id(),
            ]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses admin.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);
        $this->writeAudit('ADMIN_LOGIN_SUCCESS_WEB', $request, [
            'email' => (string) $validated['email'],
            'user_id' => Auth::id(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to($this->adminLoginUrl());
    }

    private function hasAdminPanelQuery(Request $request): bool
    {
        return (string) ($request->server('QUERY_STRING') ?? '') === '=AdminPanel';
    }

    private function adminLoginUrl(): string
    {
        return url('/admin/buildywebadmin/Login?=AdminPanel');
    }

    private function throttleKey(string $email, Request $request): string
    {
        return 'admin-login:'.mb_strtolower($email).'|'.(string) ($request->ip() ?? '0.0.0.0');
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function writeAudit(string $eventType, Request $request, array $payload): void
    {
        $this->auditLogService->write([
            'event_type' => $eventType,
            'actor_type' => 'USER',
            'actor_id' => Auth::id(),
            'entity_type' => 'ADMIN_AUTH',
            'entity_id' => Auth::id(),
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => $payload,
            'occurred_at' => now(),
        ]);
    }
}
