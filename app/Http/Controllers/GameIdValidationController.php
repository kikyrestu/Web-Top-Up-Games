<?php

namespace App\Http\Controllers;

use App\Services\GameIdValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class GameIdValidationController extends Controller
{
    /**
     * Validate a game ID and return the player nickname.
     * POST /api/validate-game-id
     */
    public function validate(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'user_id' => 'required|string|max:100',
            'zone_id' => 'nullable|string|max:50',
        ]);

        // Rate limit: 10 requests per minute per IP
        $key = 'game-id-check:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'nickname' => '',
                'message' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $validator = new GameIdValidatorService();
        $result = $validator->validate(
            $request->input('category_name'),
            $request->input('user_id'),
            $request->input('zone_id')
        );

        return response()->json($result);
    }

    /**
     * Check if a category supports ID validation.
     * GET /api/validate-game-id/check?category_name=xxx
     */
    public function check(Request $request)
    {
        $categoryName = $request->query('category_name', '');
        $validator = new GameIdValidatorService();
        $game = $validator->detectGameFromCategory($categoryName);

        return response()->json([
            'supported' => $game !== null,
            'game' => $game,
        ]);
    }
}
