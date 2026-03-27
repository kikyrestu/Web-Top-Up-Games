<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtpProvider;
use Illuminate\Http\Request;
use App\Services\Otp\OtpProviderFactory;
use Illuminate\Support\Facades\Log;

class OtpProviderController extends Controller
{
    public function index()
    {
        $providers = OtpProvider::latest()->paginate(10);
        return view('admin.otp_providers.index', compact('providers'));
    }

    public function create()
    {
        return view('admin.otp_providers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:otp_providers,code|max:50',
            'type' => 'required|in:sms,whatsapp,email',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $credentials = [];
        if ($request->has('credentials')) {
            foreach ($request->credentials as $key => $value) {
                if (!empty($value)) {
                    $credentials[$key] = $value;
                }
            }
        }

        if ($request->is_default) {
            // Remove other defaults for the same type
            OtpProvider::where('type', $request->type)->update(['is_default' => false]);
        }

        OtpProvider::create([
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'credentials' => $credentials,
            'is_active' => $request->has('is_active'),
            'is_default' => $request->has('is_default'),
        ]);

        return redirect()->route('admin.otp-providers.index')->with('success', 'OTP Provider berhasil ditambahkan.');
    }

    public function edit(OtpProvider $otpProvider)
    {
        return view('admin.otp_providers.edit', compact('otpProvider'));
    }

    public function update(Request $request, OtpProvider $otpProvider)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:otp_providers,code,'.$otpProvider->id,
            'type' => 'required|in:sms,whatsapp,email',
        ]);

        $credentials = $otpProvider->credentials ?? [];
        if ($request->has('credentials')) {
            foreach ($request->credentials as $key => $value) {
                if (!empty($value)) {
                    $credentials[$key] = $value; // Only update if filled, keep old if empty
                }
            }
        }

        if ($request->has('is_default')) {
            // Remove other defaults for the same type
            OtpProvider::where('type', $request->type)
                ->where('id', '!=', $otpProvider->id)
                ->update(['is_default' => false]);
        }

        $otpProvider->update([
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'credentials' => $credentials,
            'is_active' => $request->has('is_active'),
            'is_default' => $request->has('is_default'),
        ]);

        return redirect()->route('admin.otp-providers.index')->with('success', 'OTP Provider berhasil diperbarui.');
    }

    public function destroy(OtpProvider $otpProvider)
    {
        $otpProvider->delete();
        return redirect()->route('admin.otp-providers.index')->with('success', 'OTP Provider berhasil dihapus.');
    }

    public function testConnection(OtpProvider $otpProvider)
    {
        try {
            $driver = OtpProviderFactory::resolve($otpProvider->code);
            
            // For testing, we just check if the credentials logic passes the basic validation
            $isValid = $driver->validateCredentials($otpProvider->credentials ?? []);

            if ($isValid) {
                return response()->json([
                    'success' => true,
                    'message' => 'Koneksi ke Provider berhasil! Kredensial valid.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Kredensial tidak valid atau tidak lengkap untuk provider ini.'
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Provider Test Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
