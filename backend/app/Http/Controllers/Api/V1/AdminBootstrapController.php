<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class AdminBootstrapController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (User::query()->where('role', 'admin')->exists()) {
            return response()->json([
                'success' => false,
                'code' => 'ADMIN_ALREADY_EXISTS',
                'message' => 'Bootstrap is disabled because admin already exists',
                'data' => null,
            ], 409);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'setup_key' => ['required', 'string', 'min:10'],
        ]);

        $expectedKey = (string) config('app.admin_bootstrap_key', '');

        if ($expectedKey === '' || !hash_equals($expectedKey, (string) $validated['setup_key'])) {
            return response()->json([
                'success' => false,
                'code' => 'INVALID_SETUP_KEY',
                'message' => 'Invalid setup key',
                'data' => null,
            ], 403);
        }

        $admin = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make((string) $validated['password']),
            'role' => 'admin',
        ]);

        return response()->json([
            'success' => true,
            'code' => 'ADMIN_BOOTSTRAPPED',
            'message' => 'Admin account created successfully',
            'data' => [
                'id' => (int) $admin->id,
                'name' => (string) $admin->name,
                'email' => (string) $admin->email,
                'role' => (string) $admin->role,
            ],
        ], 201);
    }
}
