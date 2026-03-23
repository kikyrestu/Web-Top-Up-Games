<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AdminAuthController extends Controller
{
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

        if (!Auth::attempt($validated)) {
            return back()->withErrors([
                'email' => 'Email atau password tidak valid.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ((string) (Auth::user()?->role ?? 'user') !== 'admin') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun ini tidak memiliki akses admin.',
            ])->onlyInput('email');
        }

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
}
