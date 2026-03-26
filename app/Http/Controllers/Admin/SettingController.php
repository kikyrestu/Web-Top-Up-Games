<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'contact_whatsapp' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'markup_percentage' => 'nullable|numeric|min:0|max:100',
            'pricing_mode' => 'nullable|in:manual,cheapest_auto',
            'site_logo' => 'nullable|image|max:2048',
            'remove_site_logo' => 'nullable|boolean',
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
        ]);

        $settings = $request->except(['_token', '_method', 'site_logo']);

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

        foreach ($settings as $key => $value) {
            if ($key === 'site_name' && blank($value)) {
                $value = 'PPOBKu';
            }
            Setting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }
}
