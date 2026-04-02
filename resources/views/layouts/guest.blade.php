<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Setting::get('site_name', config('app.name', 'PPOBKu')) }}</title>

        @php
            $rawFav = \App\Models\Setting::get('site_favicon');
            $rawFav192 = \App\Models\Setting::get('site_favicon_192');
            $rawFav180 = \App\Models\Setting::get('site_favicon_180');
            $fav32 = $rawFav ? asset('storage/' . $rawFav) : asset('favicon.ico');
            $fav192 = $rawFav192 ? asset('storage/' . $rawFav192) : $fav32;
            $fav180 = $rawFav180 ? asset('storage/' . $rawFav180) : $fav32;
        @endphp
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $fav32 }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ $fav192 }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $fav180 }}">
        <meta name="msapplication-TileImage" content="{{ $fav192 }}">

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
    <style>
        @media (min-width: 1024px) {
            .mobile-logo-wrapper { display: none !important; }
        }
    </style>
    <body class="antialiased text-white overflow-x-hidden relative min-h-screen lg:overflow-hidden" style="background-color: #121212;">
        <!-- Background Glow Effects -->
        <div class="fixed pointer-events-none z-0" style="top: -100px; right: -50px; width: 300px; height: 300px; background: #f97316; border-radius: 50%; filter: blur(140px); opacity: 0.1;"></div>
        <div class="fixed pointer-events-none z-0" style="bottom: -100px; left: -50px; width: 300px; height: 300px; background: #f97316; border-radius: 50%; filter: blur(140px); opacity: 0.1;"></div>

        <div class="min-h-screen lg:h-screen flex flex-col lg:flex-row w-full relative z-10 lg:overflow-hidden" style="min-height: 100vh; display: flex;">
            <!-- Left Side / Branding (Hidden on Mobile, Visible on Desktop lg+) -->
            <div class="hidden lg:flex w-1/2 lg:h-full flex-col relative overflow-hidden" style="background-color: #0f1118; border-right: 1px solid #2d2d2d;">
                @php
                    $authCover = \App\Models\Setting::get('auth_cover_image');
                    $authTitle = \App\Models\Setting::get('auth_title');
                    $authSubtitle = \App\Models\Setting::get('auth_subtitle');
                    $siteLogo = \App\Models\Setting::get('site_logo');
                    $siteName = \App\Models\Setting::get('site_name', 'PPOBKu');
                @endphp

                <!-- Inner glow gradient or Custom Background Image -->
                @if($authCover)
                <div class="absolute inset-0 z-0">
                    <img src="{{ asset('storage/' . $authCover) }}" class="w-full h-full object-cover" alt="Auth Background">
                    <div class="absolute inset-0 bg-black/60"></div>
                </div>
                @else
                <div class="absolute inset-0 z-0" style="background: linear-gradient(135deg, #1a1c23, #0f1118);"></div>
                @endif
                
                <!-- Pattern Overlay -->
                <div class="absolute inset-0 z-0 opacity-5" style="background-image: radial-gradient(#f97316 1px, transparent 1px); background-size: 20px 20px;"></div>

                <!-- Scrollable Inner Content -->
                <div class="relative z-10 w-full h-full overflow-y-auto pb-12 pt-12">
                    <div class="flex flex-col justify-center items-center min-h-full px-12">
                        @if($siteLogo)
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-24 mx-auto mb-8" style="filter: drop-shadow(0 0 15px rgba(249,115,22,0.4));">
                        @endif
                        
                        @if(!empty($authTitle))
                        <h1 class="text-4xl xl:text-5xl font-black text-center text-white italic tracking-widest mb-4" style="margin-bottom: 1rem;">{{ $authTitle }}</h1>
                        @endif
                        
                        @if(!empty($authSubtitle))
                        <p class="text-gray-400 text-center leading-relaxed mt-4" style="font-size: 1.125rem; margin-top: 1rem;">{!! nl2br(e($authSubtitle)) !!}</p>
                        @endif
                        
                        <!-- Advanced Custom HTML Render -->
                        @if($authHtml = \App\Models\Setting::get('auth_custom_html'))
                            <div class="mt-8 text-left border-t border-dark-700/50 pt-6 prose prose-invert mx-auto w-full">
                                {!! $authHtml !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Side / Form Container -->
            <div class="w-full lg:w-1/2 min-h-screen lg:min-h-0 lg:h-full flex flex-col justify-center items-center p-4 sm:p-8 relative lg:overflow-y-auto">
                
                <!-- Mobile Logo (Visible only on mobile/tablet) -->
                <div class="mobile-logo-wrapper mb-8 text-center mt-2" style="margin-bottom: 2rem;">
                    <a href="/" class="flex flex-col items-center gap-2 group">
                        @php
                            $siteLogo = \App\Models\Setting::get('site_logo');
                            $siteName = \App\Models\Setting::get('site_name', 'PPOBKu');
                        @endphp
                        @if($siteLogo)
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="h-12 sm:h-14" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.5));">
                        @endif
                        <span class="text-2xl font-black text-white italic tracking-wide transition">{{ $siteName }}</span>
                    </a>
                </div>

                <!-- Form Card wrapper -->
                <div class="w-full sm:max-w-md border border-[#2d2d2d] rounded-2xl p-6 sm:p-8 relative z-10" style="background-color: #1a1c23; max-width: 28rem; width: 100%; box-shadow: 0 25px 80px rgba(0,0,0,0.6); backdrop-filter: blur(8px);">
                    {{ $slot }}
                </div>

                <!-- Footer -->
                <p class="text-xs text-gray-600 mt-8 text-center relative z-10" style="margin-top: 2rem;">&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
            </div>
        </div>
    </body>
</html>
