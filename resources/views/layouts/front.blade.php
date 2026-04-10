<!DOCTYPE html>
<html lang="id">
<head>
        <?php
                $seoSiteName = trim($global_site_name ?? 'PPOBKu');
                $seoDefaultDescription = trim($global_site_description ?? 'Top up game dan PPOB cepat, aman, dan terpercaya.');
                $seoPageTitle = trim($__env->yieldContent('title', 'Beranda'));
                $seoMetaTitle = $seoPageTitle !== '' ? $seoPageTitle . ' - ' . $seoSiteName : $seoSiteName;
                $seoMetaDescription = trim($__env->yieldContent('meta_description', $seoDefaultDescription));
                $seoCanonical = trim($__env->yieldContent('canonical', url()->current()));
                $seoRobots = trim($__env->yieldContent('robots', 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'));
                $seoImage = trim($__env->yieldContent('meta_image', !empty($global_site_logo) ? asset('storage/' . $global_site_logo) : asset('favicon.ico')));
                $seoLogoUrl = !empty($global_site_logo) ? asset('storage/' . $global_site_logo) : asset('favicon.ico');
                $seoImageUrl = preg_match('/^https?:\/\//i', $seoImage) ? $seoImage : url($seoImage);

                // FAVICON
                $fav32 = !empty($global_site_favicon) ? asset('storage/' . $global_site_favicon) : asset('favicon.ico');
                $fav192 = !empty($global_site_favicon_192) ? asset('storage/' . $global_site_favicon_192) : $fav32;
                $fav180 = !empty($global_site_favicon_180) ? asset('storage/' . $global_site_favicon_180) : $fav32;

                $waDigits = preg_replace('/\D+/', '', (string) ($global_contact_whatsapp ?? ''));
                $waDigits = ($waDigits !== '' && str_starts_with($waDigits, '0')) ? '62' . substr($waDigits, 1) : $waDigits;
                $waLink = $waDigits !== '' ? 'https://wa.me/' . $waDigits : route('front.page', 'kontak');

                $seoContactPoints = array_values(array_filter([
                    $waDigits !== '' ? ['@type' => 'ContactPoint', 'contactType' => 'customer support', 'telephone' => '+' . $waDigits, 'availableLanguage' => ['id', 'en']] : null,
                    !empty($global_contact_email) ? ['@type' => 'ContactPoint', 'contactType' => 'customer support', 'email' => $global_contact_email, 'availableLanguage' => ['id', 'en']] : null,
                ]));

                $organizationSchema = ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => $seoSiteName, 'url' => url('/'), 'logo' => $seoLogoUrl];
                $organizationSchema = !empty($seoContactPoints) ? array_merge($organizationSchema, ['contactPoint' => $seoContactPoints]) : $organizationSchema;

                $websiteSchema = [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $seoSiteName,
                    'url' => url('/'),
                    'potentialAction' => ['@type' => 'SearchAction', 'target' => route('front.index') . '?q={search_term_string}', 'query-input' => 'required name=search_term_string'],
                ];
        ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $seoMetaTitle }}</title>
        <meta name="description" content="{{ $seoMetaDescription }}">
        <meta name="robots" content="{{ $seoRobots }}">
        <link rel="canonical" href="{{ $seoCanonical }}">
        <link rel="alternate" href="{{ $seoCanonical }}" hreflang="id-ID">
        <link rel="alternate" href="{{ $seoCanonical }}" hreflang="x-default">
        <link rel="alternate" type="application/rss+xml" title="{{ $seoSiteName }} Feed" href="{{ route('front.feed') }}">

        <!-- FAVICON TAGS -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $fav32 }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ $fav192 }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $fav180 }}">
        <meta name="msapplication-TileImage" content="{{ $fav192 }}">

        <meta property="og:type" content="website">
        <meta property="og:locale" content="id_ID">
        <meta property="og:site_name" content="{{ $seoSiteName }}">
        <meta property="og:title" content="{{ $seoMetaTitle }}">
        <meta property="og:description" content="{{ $seoMetaDescription }}">
        <meta property="og:url" content="{{ $seoCanonical }}">
        <meta property="og:image" content="{{ $seoImageUrl }}">
        <meta property="og:image:secure_url" content="{{ $seoImageUrl }}">
        <meta property="og:image:alt" content="{{ $seoMetaTitle }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seoMetaTitle }}">
        <meta name="twitter:description" content="{{ $seoMetaDescription }}">
        <meta name="twitter:image" content="{{ $seoImageUrl }}">
        <meta name="twitter:image:alt" content="{{ $seoMetaTitle }}">

        <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        <script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @stack('jsonld')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #111620; color: #ffffff; }
        .hide-scroll::-webkit-scrollbar { display: none; }
        .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #11131c; }
        ::-webkit-scrollbar-thumb { background: #343b54; border-radius: 4px; }
        @media (min-width: 1024px) { .mobile-bottom-nav { display: none !important; } }
        @media (max-width: 767px) { .wa-floating { bottom: 6.5rem !important; } }
    </style>
</head>
<body class="antialiased flex flex-col min-h-screen">

    <!-- Topbar -->
    <div class="bg-up-darkest border-b border-up-border/30 hidden sm:block">
        <div class="max-w-[1280px] mx-auto text-[#8a94ad] text-[11px] font-semibold py-2.5 px-4 flex justify-between items-center tracking-wider">
            <div>INSTANT TOP UP! INSTANT PLAY!</div>
            <div class="flex items-center space-x-3">
                <span class="flex items-center"><img src="https://flagcdn.com/w20/id.png" alt="Bendera Indonesia" width="20" height="15" loading="lazy" decoding="async" class="w-4 h-3 mr-1.5 rounded-sm"> Indonesia - IDR</span>
            </div>
        </div>
    </div>

    <!-- Main Navbar -->
    <header class="bg-up-nav sticky top-0 z-50 shadow-lg border-b border-up-border/30">
        <div class="max-w-[1280px] mx-auto px-4 py-4 sm:py-5">
            <div class="flex items-center justify-between gap-4">
                
                <!-- Left: Logo -->
                <a href="{{ route('front.index') }}" class="flex items-center gap-2.5 shrink-0">
                    @if(!empty($global_site_logo))
                        <img src="{{ asset('storage/' . $global_site_logo) }}" alt="{{ $global_site_name ?? 'Logo' }}" width="144" height="36" fetchpriority="high" decoding="async" class="h-10 w-auto object-contain">
                    @endif
                    <span class="text-white text-xl font-black tracking-tight hidden sm:block">{{ $global_site_name ?? 'PPOBKu' }}</span>
                </a>

                <!-- Center: Nav Links (desktop) -->
                <nav class="hidden lg:flex items-center gap-1 flex-1 justify-center" x-data="{ moreOpen: false }">
                    <a href="{{ route('front.cek-pesanan') }}" class="px-3 py-2 rounded-lg text-[13px] font-semibold text-gray-300 hover:text-white hover:bg-white/5 transition whitespace-nowrap">
                        <i class="fas fa-receipt mr-1.5 text-[11px]"></i>Cek Pesanan
                    </a>
                    <a href="{{ route('front.page', 'daftar-harga') }}" class="px-3 py-2 rounded-lg text-[13px] font-semibold text-gray-300 hover:text-white hover:bg-white/5 transition whitespace-nowrap">
                        <i class="fas fa-list-alt mr-1.5 text-[11px]"></i>Daftar Harga
                    </a>
                    <a href="{{ route('front.article.index') }}" class="px-3 py-2 rounded-lg text-[13px] font-semibold text-gray-300 hover:text-white hover:bg-white/5 transition whitespace-nowrap">
                        <i class="fas fa-newspaper mr-1.5 text-[11px]"></i>Artikel
                    </a>
                    <a href="{{ route('front.promo') }}" class="px-3 py-2 rounded-lg text-[13px] font-semibold text-[#f97316] hover:bg-[#f97316]/10 transition whitespace-nowrap">
                        <i class="fas fa-tag mr-1.5 text-[11px]"></i>Promo
                    </a>
                    
                    <!-- Lainnya Dropdown -->
                    <div class="relative" @mouseenter="moreOpen = true" @mouseleave="moreOpen = false">
                        <button class="px-3 py-2 rounded-lg text-[13px] font-semibold text-gray-400 hover:text-white hover:bg-white/5 transition flex items-center gap-1.5 focus:outline-none whitespace-nowrap">
                            Lainnya <i class="fas fa-caret-down text-[9px] transition-transform" :class="moreOpen ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="moreOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1" class="absolute left-1/2 -translate-x-1/2 top-full mt-2 w-52 bg-up-card border border-up-border rounded-xl shadow-2xl py-1.5 z-50" style="display: none;">
                            <a href="{{ route('front.calculator') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-calculator w-4 text-center text-gray-500"></i> Kalkulator</a>
                            <a href="{{ route('front.page', 'kontak') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-headset w-4 text-center text-gray-500"></i> Hubungi CS</a>
                            <div class="border-t border-up-border/40 my-1.5"></div>
                            <a href="{{ route('front.page', 'syarat-ketentuan') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-book-open w-4 text-center text-gray-500"></i> Syarat & Ketentuan</a>
                            <a href="{{ route('front.page', 'kebijakan-privasi') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-shield-alt w-4 text-center text-gray-500"></i> Kebijakan Privasi</a>
                        </div>
                    </div>
                </nav>

                <!-- Right: Search + Auth -->
                <div class="flex items-center gap-3 shrink-0">
                    <form action="{{ route('front.index') }}" method="GET" class="hidden lg:block relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari game atau produk..." class="bg-[#111620] border border-up-border text-white text-sm rounded-lg w-[220px] pl-10 pr-4 py-2 focus:outline-none focus:border-up-yellow focus:ring-1 focus:ring-up-yellow transition-colors placeholder-gray-500">
                    </form>

                    @auth
                        <div class="relative" x-data="{ userMenu: false }" @mouseenter="userMenu = true" @mouseleave="userMenu = false">
                            <button class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-white/5 transition focus:outline-none">
                                <img src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=f97316&color=fff&size=40' }}" alt="Avatar" class="w-10 h-10 rounded-full border border-up-border shadow-sm">
                                <div class="hidden md:flex flex-col items-start min-w-[80px]">
                                    <span class="text-white text-sm font-bold leading-tight">{{ auth()->user()->name }}</span>
                                    <span class="text-xs text-gray-400 leading-tight">Rp {{ number_format(auth()->user()->wallet_balance ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <i class="fas fa-caret-down text-gray-500 text-xs hidden md:block ml-1"></i>
                            </button>
                            <div x-show="userMenu" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1" class="absolute right-0 mt-2 w-56 bg-up-card border border-up-border rounded-xl shadow-2xl overflow-hidden z-50" style="display: none;">
                                <div class="px-4 py-3 bg-[#191d2c] border-b border-up-border/50">
                                    <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-mono mt-0.5 truncate">{{ auth()->user()->email ?? auth()->user()->phone ?? 'Guest' }}</p>
                                </div>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-xs text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-shield-alt w-5 text-center mr-1"></i> Admin Panel</a>
                                    <div class="border-t border-up-border/50"></div>
                                @endif
                                <a href="{{ route('member.dashboard') }}" class="block px-4 py-2.5 text-xs text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-home w-5 text-center mr-1"></i> Dashboard</a>
                                <a href="{{ route('member.wallet') }}" class="block px-4 py-2.5 text-xs text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-wallet w-5 text-center mr-1"></i> Deposit Saldo</a>
                                <a href="{{ route('member.transactions') }}" class="block px-4 py-2.5 text-xs text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-history w-5 text-center mr-1"></i> Riwayat Transaksi</a>
                                <a href="{{ route('member.favorites') }}" class="block px-4 py-2.5 text-xs text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-heart w-5 text-center mr-1 text-rose-500"></i> Game Favorit</a>
                                <a href="{{ route('member.profile') }}" class="block px-4 py-2.5 text-xs text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-user-cog w-5 text-center mr-1"></i> Pengaturan</a>
                                <form action="{{ route('logout') }}" method="POST" class="border-t border-up-border/50 mt-1">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2.5 text-xs text-rose-400 hover:text-rose-300 hover:bg-rose-900/20 transition">
                                        <i class="fas fa-sign-out-alt w-5 text-center mr-1"></i> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <a href="{{ route('login') }}" class="bg-up-yellow hover:bg-[#d9831c] text-black text-sm font-semibold px-4 py-2 rounded-lg shadow-sm transition">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="hidden md:block border border-gray-600 text-gray-300 hover:border-gray-400 hover:text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                                Daftar
                            </a>
                        </div>
                    @endauth
                </div>
                
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow w-full pb-16">
        @yield('content')
    </main>

    <!-- Footer Area (Styled like image) -->
    <footer class="bg-[#181d2a] pt-12 pb-6 border-t border-up-border mt-10">
        <div class="max-w-[1280px] mx-auto px-4">
            
            <!-- Panduan FAQ & Links -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
                <div class="md:col-span-2 space-y-6">
                    <h3 class="text-white text-2xl font-bold">Panduan FAQ Terlengkap Anda</h3>
                    <p class="text-up-textmuted text-sm leading-relaxed max-w-md">Dapatkan semua jawaban atas pertanyaan Anda di sini. Jelajahi FAQ kami untuk semua yang Anda butuhkan guna memaksimalkan pengalaman bermain game Anda.</p>
                    <a href="{{ route('front.page', 'faq') }}" class="inline-block border border-up-border text-white text-sm px-6 py-2 rounded hover:bg-up-card transition">Lihat Semua</a>
                    
                    <!-- Accordion Mockup -->
                    <div class="space-y-2 mt-6 max-w-lg">
                        <div class="border border-up-border bg-[#1d2235] p-3 flex justify-between items-center rounded cursor-pointer hover:border-gray-500">
                            <span class="text-xs text-gray-300">Voucher UniPin di Indomaret Invalid.</span> <i class="fas fa-plus text-gray-500"></i>
                        </div>
                        <div class="border border-up-border bg-[#1d2235] p-3 flex justify-between items-center rounded cursor-pointer hover:border-gray-500">
                            <span class="text-xs text-gray-300">Serial dan pin tidak tercetak di indomaret.</span> <i class="fas fa-plus text-gray-500"></i>
                        </div>
                        <div class="border border-up-border bg-[#1d2235] p-3 flex justify-between items-center rounded cursor-pointer hover:border-gray-500">
                            <span class="text-xs text-gray-300">Diamond belum masuk setelah beli di UniPin.</span> <i class="fas fa-plus text-gray-500"></i>
                        </div>
                    </div>
                </div>

                <!-- Footer Links -->
                <div>
                    <h4 class="text-white text-sm font-bold mb-6">Produk dan Layanan</h4>
                    <ul class="text-up-textmuted text-xs space-y-3">
                        <li><a href="{{ route('front.index') }}" class="hover:text-white transition">Game</a></li>
                        <li><a href="{{ route('front.index') }}" class="hover:text-white transition">Voucher</a></li>
                        <li><a href="{{ route('front.article.index') }}" class="hover:text-white transition">SEACA eSports & Community</a></li>
                        <li><a href="{{ route('front.page', 'daftar-harga') }}" class="hover:text-white transition">Saluran Pembayaran</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white text-sm font-bold mb-6">Informasi dan Dukungan</h4>
                    <ul class="text-up-textmuted text-xs space-y-3">
                        <li><a href="{{ route('front.article.index') }}" class="hover:text-white transition">UP Station Media</a></li>
                        <li><a href="{{ route('front.article.index') }}" class="hover:text-white transition">Promo dan Acara</a></li>
                        <li><a href="{{ route('front.page', 'faq') }}" class="hover:text-white transition">FAQ</a></li>
                        <li><a href="{{ route('front.page', 'kontak') }}" class="hover:text-white transition">Dukungan Pelanggan</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-up-border pt-6 flex flex-col md:flex-row justify-between items-center text-[10px] text-up-textmuted">
                <p>&copy; {{ date('Y') }} {{ $global_site_name ?? 'PPOBKu' }}. Semua Hak Cipta | <a href="{{ route('front.page', 'syarat-ketentuan') }}" class="text-up-yellow hover:underline">Terms & Conditions</a> | <a href="{{ route('front.page', 'kebijakan-privasi') }}" class="text-up-yellow hover:underline">Privacy Policy</a></p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp -->
    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="wa-floating fixed bottom-6 right-6 w-14 h-14 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg hover:scale-110 transition z-50">
        <i class="fab fa-whatsapp text-3xl"></i>
    </a>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-up-nav border-t border-up-border z-50 px-6 py-3 flex justify-between items-center text-[10px] font-bold text-gray-400" style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom)); box-shadow: 0 -4px 10px rgba(0,0,0,0.4);">
        <a href="{{ route('front.index') }}" class="flex flex-col items-center gap-1 min-w-[50px] {{ request()->routeIs('front.index') ? 'text-up-yellow' : 'hover:text-white' }}">
            <i class="fas fa-home text-xl mb-0.5"></i>
            <span>Beranda</span>
        </a>
        <a href="{{ route('front.cek-pesanan') }}" class="flex flex-col items-center gap-1 min-w-[50px] {{ request()->routeIs('front.cek-pesanan') ? 'text-up-yellow' : 'hover:text-white' }}">
            <i class="fas fa-search-dollar text-xl mb-0.5"></i>
            <span>Pesanan</span>
        </a>
        <a href="{{ route('front.page', 'daftar-harga') }}" class="flex flex-col items-center gap-1 min-w-[50px] {{ request()->routeIs('front.page', 'daftar-harga') ? 'text-up-yellow' : 'hover:text-white' }}">
            <i class="fas fa-list-alt text-xl mb-0.5"></i>
            <span>Harga</span>
        </a>
        @auth
        <a href="{{ route('member.dashboard') }}" class="flex flex-col items-center gap-1 min-w-[50px] {{ request()->is('member*') ? 'text-up-yellow' : 'hover:text-white' }}">
            <i class="fas fa-user-circle text-xl mb-0.5"></i>
            <span>Akun</span>
        </a>
        @else
        <a href="{{ route('login') }}" class="flex flex-col items-center gap-1 min-w-[50px] hover:text-white">
            <i class="fas fa-sign-in-alt text-xl mb-0.5"></i>
            <span>Masuk</span>
        </a>
        @endauth
    </div>

    @stack('scripts')
</body>
</html>