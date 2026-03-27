<?php

namespace App\Services\Otp\Drivers;

use App\Services\Otp\OtpProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailgunEmail implements OtpProviderInterface
{
    public function sendOtp(string $target, string $code, array $credentials): bool
    {
        $domain = $credentials['domain'] ?? '';
        $secret = $credentials['secret'] ?? '';
        $from = $credentials['from_address'] ?? 'noreply@' . $domain;
        $siteName = config('app.name');

        if (empty($domain) || empty($secret)) {
            Log::error('Mailgun credentials missing.');
            return false;
        }

        $message = "Kode OTP registrasi Anda adalah: {$code}. Jangan memberikan kode ini ke siapapun. Berlaku selama 5 menit.";

        try {
            $response = Http::withBasicAuth('api', $secret)
                ->asForm()
                ->post("https://api.mailgun.net/v3/{$domain}/messages", [
                    'from' => "{$siteName} <{$from}>",
                    'to' => $target,
                    'subject' => "Kode OTP Anda - {$siteName}",
                    'text' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Mailgun Send Error: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('Mailgun Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function validateCredentials(array $credentials): bool
    {
        return !empty($credentials['domain']) && !empty($credentials['secret']);
    }
}
