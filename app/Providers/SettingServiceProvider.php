<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            View::composer('*', function ($view) {
                $site_name = Setting::get('site_name', 'PPOBKu');
                $site_logo = Setting::get('site_logo', null);
                $site_favicon = Setting::get('site_favicon', null);
                $site_favicon_192 = Setting::get('site_favicon_192', null);
                $site_favicon_180 = Setting::get('site_favicon_180', null);
                $site_description = Setting::get('site_description', 'Top up game dan PPOB cepat, aman, dan terpercaya.');
                $contact_whatsapp = Setting::get('contact_whatsapp', null);
                $contact_email = Setting::get('contact_email', null);
                
                $view->with('global_site_name', $site_name);
                $view->with('global_site_logo', $site_logo);
                $view->with('global_site_favicon', $site_favicon);
                $view->with('global_site_favicon_192', $site_favicon_192);
                $view->with('global_site_favicon_180', $site_favicon_180);
                $view->with('global_site_description', $site_description);
                $view->with('global_contact_whatsapp', $contact_whatsapp);
                $view->with('global_contact_email', $contact_email);
            });
        }
    }
}
