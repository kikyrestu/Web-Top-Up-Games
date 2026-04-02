<?php

namespace App\Services\Otp\Drivers;

use App\Services\Otp\OtpProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config; // Added this line

class SendGridEmail implements OtpProviderInterface
{
    public function sendOtp(string $target, string $code, array $credentials): bool
    {
        $apiKey = $credentials['api_key'] ?? '';
        $fromEmail = $credentials['from_address'] ?? '';
        
        $siteName = \App\Models\Setting::get('site_name', config('app.name'));
        $fromName = $credentials['from_name'] ?? $siteName;

        if (empty($apiKey) || empty($fromEmail)) {
            Log::error('SendGrid credentials missing.');
            return false;
        }

        $subject = "Kode OTP Anda - {$fromName}";
        $message = "Kode OTP registrasi Anda adalah: {$code}. Jangan memberikan kode ini ke siapapun. Berlaku selama 5 menit.";

        $template = \App\Models\Setting::get('email_otp_template');
        if (empty($template)) {
            $template = $message;
        }

        $htmlMessage = str_replace(
            ['{OTP}', '{APP_NAME}'],
            [$code, $fromName],
            $template
        );

        try {
            $response = Http::withToken($apiKey)->post('https://api.sendgrid.com/v3/mail/send', [
                'personalizations' => [
                    [
                        'to' => [['email' => $target]],
                    ]
                ],
                'from' => [
                    'email' => $fromEmail,
                    'name' => $fromName,
                ],
                'subject' => $subject,
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $htmlMessage,
                    ]
                ],
            ]);

            // SendGrid returns 202 Accepted on success
            if ($response->status() === 202 || $response->successful()) {
                return true;
            }

            Log::error('SendGrid Send Error: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('SendGrid Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function validateCredentials(array $credentials): bool
    {
        return !empty($credentials['api_key']);
    }
}
