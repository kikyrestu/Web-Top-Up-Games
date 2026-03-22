<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ValidationController extends Controller
{
    public function gameId(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game' => ['required', 'string', 'max:100'],
            'user_id' => ['required', 'string', 'max:100'],
            'server_id' => ['nullable', 'string', 'max:100'],
        ]);

        return response()->json([
            'success' => true,
            'code' => 'GAME_ID_VALIDATED',
            'message' => 'Game ID validation request accepted',
            'data' => [
                'game' => $validated['game'],
                'user_id' => $validated['user_id'],
                'server_id' => $validated['server_id'] ?? null,
                'is_valid' => true,
                'nickname' => null,
            ],
        ]);
    }
}
