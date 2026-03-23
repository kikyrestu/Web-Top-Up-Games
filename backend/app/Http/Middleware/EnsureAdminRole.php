<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminRole
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return new JsonResponse([
                'success' => false,
                'code' => 'UNAUTHENTICATED',
                'message' => 'Authentication required',
                'data' => null,
            ], 401);
        }

        if ((string) ($user->role ?? 'user') !== 'admin') {
            return new JsonResponse([
                'success' => false,
                'code' => 'FORBIDDEN',
                'message' => 'Admin role required',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
