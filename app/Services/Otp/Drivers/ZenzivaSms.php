<?php

namespace App\Services\Otp\Drivers;

use App\Services\Otp\OtpProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZenzivaSms implements OtpProviderInterface
{
    public function sendOtp(string $target, string $code, array $credentials): bool
    {
        $userkey = $credentials['userkey'] ?? '';
        $passkey = $credentials['passkey'] ?? '';
        
        if (empty($userkey) || empty($passkey)) {
            Log::error('Zenziva credentials missing.');
            return false;
        }

        $message = "Kode OTP Anda: {$code}. Jgn beritahu siapapun.";

        try {
            $response = Http::post("https://console.zenziva.net/reguler/api/sendsms/", [
                'userkey' => $userkey,
                'passkey' => $passkey,
                'to' => $target,
                'message' => $message,
            ]);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] == '1') {
                return true;
            }

            Log::error('Zenziva Send Error: ' . json_encode($result));
            return false;

        } catch (\Exception $e) {
            Log::error('Zenziva Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function validateCredentials(array $credentials): bool
    {
        return !empty($credentials['userkey']) && !empty($credentials['passkey']);
    }
}
