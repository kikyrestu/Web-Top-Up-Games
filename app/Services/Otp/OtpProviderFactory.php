<?php

namespace App\Services\Otp;

use App\Models\OtpProvider;
use Illuminate\Support\Facades\Log;

class OtpProviderFactory
{
    /**
     * Resolve the appropriate OTP provider driver.
     *
     * @param OtpProvider $provider
     * @return OtpProviderInterface
     * @throws \Exception
     */
    public static function resolve(OtpProvider $provider): OtpProviderInterface
    {
        $code = strtolower($provider->code);

        switch ($code) {
            case 'fonnte':
                return new Drivers\FonnteSms();
            case 'zenziva':
                return new Drivers\ZenzivaSms();
            case 'twilio':
                return new Drivers\TwilioSms();
            case 'smtp':
                return new Drivers\SmtpEmail();
            case 'mailgun':
                return new Drivers\MailgunEmail();
            case 'sendgrid':
                return new Drivers\SendGridEmail();
            case 'mock':
                return new Drivers\MockSms();
            default:
                Log::error("Unknown OTP provider code: {$code}");
                throw new \Exception("OTP Provider driver for '{$code}' not found.");
        }
    }
}
