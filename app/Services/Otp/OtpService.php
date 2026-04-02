<?php

namespace App\Services\Otp;

use App\Models\OtpProvider;
use App\Models\OtpVerification;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Generate and send OTP to the target using an active provider.
     *
     * @param string $target Email or Phone
     * @param string $type register, login, reset
     * @return array [success: bool, message: string]
     */
    public function sendOtp(string $target, string $type = 'register'): array
    {
        // Rate Limiting: Block if sent recently (e.g., last 60 seconds)
        $recent = OtpVerification::where('target', $target)
            ->where('type', $type)
            ->where('created_at', '>=', Carbon::now()->subMinutes(1))
            ->first();

        if ($recent) {
            return ['success' => false, 'message' => 'Harap tunggu 1 menit sebelum meminta OTP baru.'];
        }

        // Determine channel type (email vs sms/wa)
        $channel = filter_var($target, FILTER_VALIDATE_EMAIL) ? 'email' : 'sms_wa'; // Using sms_wa generically for phone

        // Find active provider
        $providerQuery = OtpProvider::where('is_active', true);
        if ($channel === 'email') {
             $providerQuery->where('type', 'email');
        } else {
             $providerQuery->whereIn('type', ['sms', 'whatsapp']);
        }
        
        $provider = $providerQuery->orderByDesc('is_default')->first();

        if (!$provider) {
            Log::error("No active OTP provider found for channel: {$channel}");
            return ['success' => false, 'message' => 'Sistem pengiriman OTP sedang gangguan. Hubungi CS.'];
        }

        // Generate Code
        $length = (int) Setting::get('otp_length', 6);
        $code = str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        
        // Define Expiry
        $expiryMins = (int) Setting::get('otp_expiry_minutes', 5);

        // Delete old unverified OTPs for this target
        OtpVerification::where('target', $target)->where('type', $type)->delete();

        // Create new record (store hashed code)
        $verification = OtpVerification::create([
            'target' => $target,
            'code' => Hash::make($code),
            'type' => $type,
            'otp_provider_id' => $provider->id,
            'expires_at' => Carbon::now()->addMinutes($expiryMins),
            'is_verified' => false,
            'attempts' => 0,
        ]);

        // Dispatch via Driver
        try {
            $driver = OtpProviderFactory::resolve($provider);
            $credentials = $provider->credentials ?? [];
            
            $sent = $driver->sendOtp($target, $code, $credentials);
            
            if ($sent) {
                return ['success' => true, 'message' => 'Kode OTP telah dikirim ke ' . $target];
            }
            
            return ['success' => false, 'message' => 'Gagal mengirim OTP melalui provider.'];

        } catch (\Exception $e) {
            Log::error('OtpService dispatch error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat mengirim OTP.'];
        }
    }

    /**
     * Verify the OTP code.
     *
     * @param string $target Email or Phone
     * @param string $code Input code
     * @param string $type
     * @return array [success: bool, message: string]
     */
    public function verifyOtp(string $target, string $code, string $type = 'register'): array
    {
        $verification = OtpVerification::where('target', $target)
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->first();

        if (!$verification) {
            return ['success' => false, 'message' => 'Kode OTP tidak temukan. Silakan minta ulang.'];
        }

        if ($verification->is_verified) {
             return ['success' => true, 'message' => 'OTP sudah terverifikasi sebelumnya.'];
        }

        if ($verification->isExpired()) {
            return ['success' => false, 'message' => 'Kode OTP sudah kadaluarsa (expired).'];
        }

        if ($verification->attempts >= 5) {
            return ['success' => false, 'message' => 'Terlalu banyak percobaan salah. Silakan minta kode baru.'];
        }

        if (!Hash::check((string) $code, $verification->code)) {
            $verification->increment('attempts');
            return ['success' => false, 'message' => 'Kode OTP salah. Coba lagi.'];
        }

        // Success
        $verification->update([
            'is_verified' => true,
        ]);

        return ['success' => true, 'message' => 'Verifikasi berhasil.'];
    }
}
