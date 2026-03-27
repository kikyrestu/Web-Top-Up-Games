<?php

namespace App\Services\Otp\Drivers;

use App\Services\Otp\OtpProviderInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class SmtpEmail implements OtpProviderInterface
{
    public function sendOtp(string $target, string $code, array $credentials): bool
    {
        try {
            if ($this->hasCustomConfig($credentials)) {
                $this->temporarilyOverrideConfig($credentials);
            }

            $siteName = config('app.name');
            $message = "Kode OTP registrasi Anda adalah: {$code}. Jangan memberikan kode ini ke siapapun. Berlaku selama 5 menit.";

            Mail::raw($message, function ($mail) use ($target, $siteName) {
                $mail->to($target)->subject("Kode OTP Anda - {$siteName}");
            });

            return true;
        } catch (\Exception $e) {
            Log::error('SMTP Send Error: ' . $e->getMessage());
            return false;
        }
    }

    public function validateCredentials(array $credentials): bool
    {
        // For SMTP, basic validation of connection structure is enough. 
        // Actual handshake test is more complex and slow.
        return true; 
    }
    
    private function hasCustomConfig(array $credentials): bool
    {
        return !empty($credentials['host']) && !empty($credentials['port']);
    }
    
    private function temporarilyOverrideConfig(array $credentials): void
    {
        Config::set('mail.mailers.smtp.host', $credentials['host'] ?? config('mail.mailers.smtp.host'));
        Config::set('mail.mailers.smtp.port', $credentials['port'] ?? config('mail.mailers.smtp.port'));
        Config::set('mail.mailers.smtp.encryption', $credentials['encryption'] ?? config('mail.mailers.smtp.encryption'));
        Config::set('mail.mailers.smtp.username', $credentials['username'] ?? config('mail.mailers.smtp.username'));
        Config::set('mail.mailers.smtp.password', $credentials['password'] ?? config('mail.mailers.smtp.password'));
        
        if (!empty($credentials['from_address'])) {
            Config::set('mail.from.address', $credentials['from_address']);
        }
        if (!empty($credentials['from_name'])) {
            Config::set('mail.from.name', $credentials['from_name']);
        }
    }
}
