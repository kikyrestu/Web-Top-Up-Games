<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Security\Services\PermissionMatrixService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureWebPermission
{
    public function __construct(private readonly PermissionMatrixService $permissionMatrix)
    {
    }

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return new RedirectResponse(url('/admin/buildywebadmin/Login?=AdminPanel'));
        }

        $role = strtolower((string) ($user->role ?? 'user'));
        $allowedRoles = $this->permissionMatrix->roles();

        if (!in_array($role, $allowedRoles, true)) {
            return new RedirectResponse(route('storefront.index'));
        }

        $routeName = $request->route()?->getName();
        $permissionKey = $this->permissionMatrix->resolvePermissionKey($routeName !== null ? (string) $routeName : null);

        if ($permissionKey === null) {
            return $next($request);
        }

        if (!$this->permissionMatrix->isAllowed($role, $permissionKey)) {
            return redirect()->route('admin.dashboard')->withErrors([
                'permission' => 'Akses ditolak untuk menu/aksi ini.',
            ]);
        }

        return $next($request);
    }
}
