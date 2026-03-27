<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GuestIdentityService;
use Illuminate\Http\Request;

class GuestSessionController extends Controller
{
    protected GuestIdentityService $guestIdentityService;

    public function __construct(GuestIdentityService $guestIdentityService)
    {
        $this->guestIdentityService = $guestIdentityService;
    }

    /**
     * POST /api/v1/guest/session/init
     */
    public function init(Request $request)
    {
        try {
            $session = $this->guestIdentityService->initializeSession($request);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'identity_key' => $session->identity_key,
                    'expires_at' => $session->expires_at->toIso8601String(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menginisialisasi sesi tamu.',
            ], 400);
        }
    }
}
