<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Audit\Services\AuditLogService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class AuthTokenController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if ($user === null || !Hash::check($validated['password'], (string) $user->password)) {
            $this->auditLogService->write([
                'event_type' => 'AUTH_LOGIN_FAILED',
                'actor_type' => 'GUEST',
                'actor_id' => null,
                'entity_type' => 'USER',
                'entity_id' => $user?->id,
                'request_id' => $request->header('x-request-id'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'payload' => [
                    'email' => (string) $validated['email'],
                ],
                'occurred_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'Invalid email or password',
                'data' => null,
            ], 401);
        }

        $token = $user->createToken((string) ($validated['device_name'] ?? 'api-client'));

        $this->auditLogService->write([
            'event_type' => 'AUTH_LOGIN_SUCCESS',
            'actor_type' => 'USER',
            'actor_id' => $user->id,
            'entity_type' => 'USER',
            'entity_id' => $user->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'device_name' => (string) ($validated['device_name'] ?? 'api-client'),
            ],
            'occurred_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'code' => 'TOKEN_ISSUED',
            'message' => 'Access token issued',
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => (int) $user->id,
                    'name' => (string) $user->name,
                    'email' => (string) $user->email,
                    'role' => (string) ($user->role ?? 'user'),
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $request->user()?->currentAccessToken();
        $token?->delete();

        $this->auditLogService->write([
            'event_type' => 'AUTH_TOKEN_LOGOUT',
            'actor_type' => 'USER',
            'actor_id' => $user?->id,
            'entity_type' => 'USER',
            'entity_id' => $user?->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'scope' => 'current_token',
            ],
            'occurred_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'code' => 'TOKEN_REVOKED',
            'message' => 'Access token revoked',
            'data' => null,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'code' => 'AUTH_ME',
            'message' => 'Authenticated user loaded',
            'data' => [
                'id' => (int) $user->id,
                'name' => (string) $user->name,
                'email' => (string) $user->email,
                'role' => (string) ($user->role ?? 'user'),
            ],
        ]);
    }

    public function revokeAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $deleted = $user?->tokens()->delete() ?? 0;

        $this->auditLogService->write([
            'event_type' => 'AUTH_TOKEN_REVOKE_ALL',
            'actor_type' => 'USER',
            'actor_id' => $user?->id,
            'entity_type' => 'USER',
            'entity_id' => $user?->id,
            'request_id' => $request->header('x-request-id'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => [
                'revoked_tokens' => $deleted,
            ],
            'occurred_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'code' => 'TOKEN_ALL_REVOKED',
            'message' => 'All access tokens revoked',
            'data' => [
                'revoked_tokens' => $deleted,
            ],
        ]);
    }
}
