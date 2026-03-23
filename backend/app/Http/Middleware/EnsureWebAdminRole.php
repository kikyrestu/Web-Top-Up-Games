<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureWebAdminRole
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return new RedirectResponse(url('/admin/buildywebadmin/Login?=AdminPanel'));
        }

        if (!in_array((string) ($user->role ?? 'user'), ['admin', 'editor', 'ops', 'finance'], true)) {
            return new RedirectResponse(route('storefront.index'));
        }

        return $next($request);
    }
}
