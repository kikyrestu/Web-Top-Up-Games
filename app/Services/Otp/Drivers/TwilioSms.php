<?php

namespace App\Services\Otp\Drivers;

use App\Services\Otp\OtpProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioSms implements OtpProviderInterface
{
    public function sendOtp(string $target, string $code, array $credentials): bool
    {
        $sid = $credentials['account_sid'] ?? '';
        $token = $credentials['auth_token'] ?? '';
        $from = $credentials['from_number'] ?? '';
        
        if (empty($sid) || empty($token) || empty($from)) {
            Log::error('Twilio credentials missing.');
            return false;
        }

        $message = "Kode OTP Anda: {$code}. Jgn beritahu siapapun.";
        
        // basic formatting, assuming target might not have starting +
        if (!str_starts_with($target, '+')) {
            $target = '+' . $target;
        }

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $target,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Twilio Send Error: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('Twilio Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function validateCredentials(array $credentials): bool
    {
        return !empty($credentials['account_sid']) && !empty($credentials['auth_token']);
    }
}
