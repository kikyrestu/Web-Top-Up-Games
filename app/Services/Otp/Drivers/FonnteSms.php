<?php

namespace App\Services\Otp\Drivers;

use App\Services\Otp\OtpProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteSms implements OtpProviderInterface
{
    public function sendOtp(string $target, string $code, array $credentials): bool
    {
        $token = $credentials['token'] ?? '';
        
        if (empty($token)) {
            Log::error('Fonnte API Token missing.');
            return false;
        }

        // Normalize phone: 0812xxx → 62812xxx, +62812xxx → 62812xxx
        $target = preg_replace('/^\+/', '', $target);
        if (str_starts_with($target, '0')) {
            $target = '62' . substr($target, 1);
        }

        $message = "Kode OTP Anda adalah: {$code}. Jangan berikan kode ini kepada siapapun.";

        try {
            $response = Http::withHeaders([
                'Authorization' => $token
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
            ]);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === true) {
                return true;
            }

            Log::error('Fonnte Send Error: ' . json_encode($result));
            return false;

        } catch (\Exception $e) {
            Log::error('Fonnte Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function validateCredentials(array $credentials): bool
    {
        return !empty($credentials['token']);
    }
}
