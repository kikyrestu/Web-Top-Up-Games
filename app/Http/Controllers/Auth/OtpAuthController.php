<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Otp\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpAuthController extends Controller
{
    private OtpService $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:4', 'max:30', 'alpha_dash'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'otp_channel' => ['required', 'in:whatsapp,email'],
        ]);

        $existingUnverifiedUser = User::where(function ($query) use ($request) {
            $query->where('email', $request->email)
                  ->orWhere('whatsapp', $request->whatsapp)
                  ->orWhere('username', $request->username);
        })->where('is_verified', false)->first();

        if ($existingUnverifiedUser) {
            // Update details in case they made a typo previously
            $existingUnverifiedUser->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'whatsapp' => $request->whatsapp,
                'password' => Hash::make($request->password),
            ]);
            
            $user = $existingUnverifiedUser;
            $isResend = true;
        } else {
            // Full validation to ensure it's not taken by a VERIFIED user
            $request->validate([
                'username' => ['unique:'.User::class],
                'email' => ['unique:'.User::class],
                'whatsapp' => ['unique:'.User::class],
            ]);

            $user = User::create([
                'name' =>
                $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'whatsapp' => $request->whatsapp,
                'password' => Hash::make($request->password),
                'is_verified' => false,
            ]);
            $isResend = false;

            // Store referral code in session for after OTP verification
            if ($request->filled('referral_code')) {
                session(['pending_referral_code' => $request->referral_code]);
            }
        }

        // Target OTP based on user choice
        $channel = $request->otp_channel;
        $target = $channel === 'whatsapp' ? $user->whatsapp : $user->email;

        // Send OTP
        $response = $this->otpService->sendOtp($target, 'register');
        
        // Store session for verification logic
        session([
            'otp_target' => $target, 
            'otp_type' => 'register',
            'otp_channel' => $channel
        ]);
        
        if ($isResend) {
            $message = 'Akun ini sudah terdaftar namun belum terverifikasi. Silakan lakukan verifikasi OTP.';
            return redirect()->route('otp.verify')->with('status', $message);
        }

        if ($response['success']) {
            return redirect()->route('otp.verify')->with('status', $response['message']);
        }
        
        // If fail, still redirect but show error
        return redirect()->route('otp.verify')->with('error', $response['message']);
    }

    public function showVerify()
    {
        if (!session()->has('otp_target')) {
            return redirect()->route('login');
        }

        return view('auth.verify-otp', [
            'target' => session('otp_target'),
            'type' => session('otp_type', 'register'),
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:10'],
        ]);

        $target = session('otp_target');
        $type = session('otp_type', 'register');

        if (!$target) {
            return redirect()->route('login');
        }

        $response = $this->otpService->verifyOtp($target, $request->code, $type);

        if (!$response['success']) {
            throw ValidationException::withMessages([
                'code' => $response['message'],
            ]);
        }

        // OTP Verified — lookup user by the correct field based on channel
        $channel = session('otp_channel', 'email');
        $user = $channel === 'whatsapp'
            ? User::where('whatsapp', $target)->first()
            : User::where('email', $target)->first();
        
        if ($user) {
            $user->update(['is_verified' => true]);
            Auth::login($user);
            
            // Apply referral code if present
            $pendingRef = session('pending_referral_code');
            if ($pendingRef) {
                app(\App\Services\ReferralService::class)->applyReferralCode($user, $pendingRef);
                session()->forget('pending_referral_code');
            }
            
            // Sync guest transactions
            app(\App\Services\GuestIdentityService::class)->syncTransactionsToUser($user);
            
            session()->forget(['otp_target', 'otp_type']);
            
            return redirect()->route('member.dashboard');
        }

        return redirect()->route('login')->with('error', 'Akun tidak ditemukan.');
    }

    public function resend()
    {
        $target = session('otp_target');
        $type = session('otp_type', 'register');

        if (!$target) {
            return redirect()->route('login');
        }

        $response = $this->otpService->sendOtp($target, $type);

        if ($response['success']) {
            return back()->with('status', $response['message']);
        }

        return back()->with('error', $response['message']);
    }
}
