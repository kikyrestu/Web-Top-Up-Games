<?php

namespace App\Services;

use App\Models\GuestSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GuestIdentityService
{
    /**
     * Initialize or retrieve a guest session based on a fingerprint.
     */
    public function initializeSession(Request $request): GuestSession
    {
        $request->validate([
            'fingerprint' => 'required|string|max:255',
        ]);

        $fingerprint = $request->fingerprint;
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // Find active session
        $existing = GuestSession::where('fingerprint', $fingerprint)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($existing) {
            // Extend expiry
            $existing->update(['expires_at' => Carbon::now()->addDays(30)]);
            return $existing;
        }

        // Create new
        return GuestSession::create([
            'fingerprint' => $fingerprint,
            'identity_key' => 'gks_' . Str::random(40),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'expires_at' => Carbon::now()->addDays(30),
        ]);
    }

    /**
     * Map guest transactions to real user base on contact.
     */
    public function syncTransactionsToUser(\App\Models\User $user): int
    {
        return \App\Models\Transaction::whereNull('user_id')
            ->where(function($query) use ($user) {
                $query->where('customer_contact', $user->whatsapp)
                      ->orWhere('customer_contact', $user->email);
            })
            ->update([
                'user_id' => $user->id,
            ]);
    }
}
