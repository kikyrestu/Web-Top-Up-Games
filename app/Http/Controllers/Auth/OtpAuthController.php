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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'whatsapp' => ['required', 'string', 'max:20', 'unique:'.User::class],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
            'password' => Hash::make($request->password),
            'is_verified' => false,
        ]);

        // Send OTP to WhatsApp by default
        $response = $this->otpService->sendOtp($user->whatsapp, 'register');
        
        session(['otp_target' => $user->whatsapp, 'otp_type' => 'register']);
        
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

        // OTP Verified
        $user = User::where('whatsapp', $target)->orWhere('email', $target)->first();
        
        if ($user) {
            $user->update(['is_verified' => true]);
            Auth::login($user);
            
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
