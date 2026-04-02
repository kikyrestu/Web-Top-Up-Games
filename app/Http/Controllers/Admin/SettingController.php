<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'contact_whatsapp' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'pricing_mode' => 'nullable|in:manual,cheapest_auto',
            'site_logo' => 'nullable|image|max:2048',
            'remove_site_logo' => 'nullable|boolean',
            'site_favicon' => 'nullable|image|max:1024',
            'remove_site_favicon' => 'nullable|boolean',
            // Auth Config
            'auth_title' => 'nullable|string|max:255',
            'auth_subtitle' => 'nullable|string|max:1000',
            'auth_custom_html' => 'nullable|string',
            'auth_cover_image' => 'nullable|image|max:4096',
            'remove_auth_cover' => 'nullable|boolean',
            // Commission settings
            'default_commission_type' => 'nullable|in:flat,percentage',
            'default_commission_value' => 'nullable|numeric|min:0',
            // Telegram settings
            'telegram_enabled' => 'nullable|in:0,1',
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_chat_id' => 'nullable|string|max:100',
            // WA settings
            'wa_enabled' => 'nullable|in:0,1',
            'wa_bot_url' => 'nullable|string|max:255',
            // OTP & Wallet
            'wallet_label'       => 'required|string|max:50',
            'otp_length'         => 'required|integer|min:4|max:8',
            'otp_expiry_minutes' => 'required|integer|min:1|max:60',
            'email_otp_template' => 'nullable|string',
        ]);

        // Default template if empty
        if (empty($validated['email_otp_template'])) {
            $validated['email_otp_template'] = '<div style="font-family: sans-serif; padding: 20px; text-align: center; background: #f9f9f9; border-radius: 12px; max-width: 500px; margin: auto;">'."\n"
                                             . '    <h2 style="color: #333;">Kode OTP Anda: {APP_NAME}</h2>'."\n"
                                             . '    <h1 style="background: #eef; padding: 15px 25px; display: inline-block; letter-spacing: 5px; color: #1e40af; border-radius: 8px;">{OTP}</h1>'."\n"
                                             . '    <p style="color: #666; font-size: 14px;">Jangan memberikan kode ini ke siapapun. Kode ini berlaku selama <strong style="color: #ef4444;">5 menit</strong>.</p>'."\n"
                                             . '</div>';
        }

        $settings = collect($validated)->except([
            '_token', '_method', 
            'site_logo', 'remove_site_logo', 
            'site_favicon', 'remove_site_favicon',
            'auth_cover_image', 'remove_auth_cover'
        ])->toArray();

        if ($request->hasFile('site_logo')) {
            $logoPath = $request->file('site_logo')->store('settings', 'public');
            $oldLogo = Setting::get('site_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::set('site_logo', $logoPath, 'image');
        } elseif ($request->boolean('remove_site_logo')) {
            $oldLogo = Setting::get('site_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::set('site_logo', null, 'image');
        }

        // --- FAVICON PROCESSING ---
        if ($request->hasFile('site_favicon')) {
            $file = $request->file('site_favicon');
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            
            // Crop gambar menjadi rasio 1:1 (persegi)
            $size = min($image->width(), $image->height());
            $image->crop(width: $size, height: $size);

            $folder = 'settings';
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder);
            }

            // Hapus favicon lama jika ada
            $oldFavicon32 = Setting::get('site_favicon');
            $oldFavicon192 = Setting::get('site_favicon_192');
            $oldFavicon180 = Setting::get('site_favicon_180');
            foreach ([$oldFavicon32, $oldFavicon192, $oldFavicon180] as $oldFile) {
                if ($oldFile) Storage::disk('public')->delete($oldFile);
            }

            // Buat nama unik dasar
            $baseName = 'favicon_' . uniqid();

            // 1. Favicon 32x32
            $img32 = clone $image;
            $img32->scaleDown(32, 32);
            $path32 = $folder . '/' . $baseName . '_32x32.png';
            $img32->toPng()->save(Storage::disk('public')->path($path32));
            Setting::set('site_favicon', $path32, 'image');

            // 2. Favicon 192x192
            $img192 = clone $image;
            $img192->scaleDown(192, 192);
            $path192 = $folder . '/' . $baseName . '_192x192.png';
            $img192->toPng()->save(Storage::disk('public')->path($path192));
            Setting::set('site_favicon_192', $path192, 'image');

            // 3. Favicon 180x180 (Apple Touch)
            $img180 = clone $image;
            $img180->scaleDown(180, 180);
            $path180 = $folder . '/' . $baseName . '_180x180.png';
            $img180->toPng()->save(Storage::disk('public')->path($path180));
            Setting::set('site_favicon_180', $path180, 'image');

        } elseif ($request->boolean('remove_site_favicon')) {
            $oldFavicon32 = Setting::get('site_favicon');
            $oldFavicon192 = Setting::get('site_favicon_192');
            $oldFavicon180 = Setting::get('site_favicon_180');
            foreach ([$oldFavicon32, $oldFavicon192, $oldFavicon180] as $oldFile) {
                if ($oldFile) Storage::disk('public')->delete($oldFile);
            }
            Setting::set('site_favicon', null, 'image');
            Setting::set('site_favicon_192', null, 'image');
            Setting::set('site_favicon_180', null, 'image');
        }
        // --- END FAVICON PROCESSING ---

        if ($request->hasFile('auth_cover_image')) {
            $coverPath = $request->file('auth_cover_image')->store('settings', 'public');
            $oldCover = Setting::get('auth_cover_image');
            if ($oldCover) {
                Storage::disk('public')->delete($oldCover);
            }
            Setting::set('auth_cover_image', $coverPath, 'image');
        } elseif ($request->boolean('remove_auth_cover')) {
            $oldCover = Setting::get('auth_cover_image');
            if ($oldCover) {
                Storage::disk('public')->delete($oldCover);
            }
            Setting::set('auth_cover_image', null, 'image');
        }

        foreach ($settings as $key => $value) {
            if ($key === 'site_name' && blank($value)) {
                $value = 'PPOBKu';
            }
            Setting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}
