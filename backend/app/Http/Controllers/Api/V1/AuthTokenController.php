<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class AuthTokenController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();

        if ($user === null || !Hash::check($validated['password'], (string) $user->password)) {
            return response()->json([
                'success' => false,
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'Invalid email or password',
                'data' => null,
            ], 401);
        }

        $token = $user->createToken((string) ($validated['device_name'] ?? 'api-client'));

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
        $token = $request->user()?->currentAccessToken();
        $token?->delete();

        return response()->json([
            'success' => true,
            'code' => 'TOKEN_REVOKED',
            'message' => 'Access token revoked',
            'data' => null,
        ]);
    }
}
