<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PPOB') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif !important; }
            /* Force dark inputs over Tailwind compiled defaults */
            input[type="text"], input[type="email"], input[type="password"], select {
                background-color: #0f1118 !important;
                border-color: #2d2d2d !important;
                color: #fff !important;
            }
            input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, select:focus {
                border-color: #f97316 !important;
                box-shadow: 0 0 0 2px rgba(249,115,22,0.2) !important;
                --tw-ring-color: #f97316 !important;
            }
            input::placeholder { color: #6b7280 !important; }
        </style>
    </head>
    <body class="antialiased" style="background: #121212; color: #fff;">
        <!-- Background Glow Effects -->
        <div style="position:fixed;top:-100px;right:-50px;width:300px;height:300px;background:#f97316;border-radius:50%;filter:blur(140px);opacity:0.07;pointer-events:none;"></div>
        <div style="position:fixed;bottom:-100px;left:-50px;width:300px;height:300px;background:#f97316;border-radius:50%;filter:blur(140px);opacity:0.05;pointer-events:none;"></div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="position:relative;z-index:10;">
            <!-- Logo -->
            <div class="mb-2">
                <a href="/" class="flex items-center gap-3 group">
                    @php
                        $siteLogo = \App\Models\Setting::get('site_logo');
                        $siteName = \App\Models\Setting::get('site_name', 'PPOB');
                    @endphp
                    @if($siteLogo)
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-10">
                    @endif
                    <span class="text-2xl font-black text-white italic tracking-wide group-hover:text-[#f97316] transition">{{ $siteName }}</span>
                </a>
            </div>

            <!-- Card -->
            <div class="w-full sm:max-w-md mt-4 px-6 py-6 overflow-hidden sm:rounded-2xl" style="background:#1c1c1c;border:1px solid #2d2d2d;box-shadow:0 25px 80px rgba(0,0,0,0.5);">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <p class="text-xs text-gray-600 mt-6">&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
        </div>
    </body>
</html>
