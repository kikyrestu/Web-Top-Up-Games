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
    </style>
</head>
<body class="antialiased flex flex-col min-h-screen">

    <!-- Topbar -->
    <div class="bg-up-darkest text-[#8a94ad] text-[10px] font-semibold py-1.5 px-4 flex justify-between items-center tracking-wider">
        <div>INSTANT TOP UP! INSTANT PLAY!</div>
        <div class="flex items-center space-x-3">
            <span class="flex items-center"><img src="https://flagcdn.com/w20/id.png" alt="Bendera Indonesia" width="20" height="15" loading="lazy" decoding="async" class="w-4 h-3 mr-1"> Indonesia - IDR</span>
        </div>
    </div>

    <!-- Main Navbar -->
    <header class="bg-up-nav sticky top-0 z-50 shadow-md border-b border-up-border/50">
        <div class="max-w-[1280px] mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                
                <!-- Left: Logo & Nav Links -->
                <div class="flex items-center flex-1">
                    <a href="{{ route('front.index') }}" class="mr-8 flex items-center gap-2">
                        @if(!empty($global_site_logo))
                            <img src="{{ asset('storage/' . $global_site_logo) }}" alt="{{ $global_site_name ?? 'Logo' }}" width="144" height="36" fetchpriority="high" decoding="async" class="h-9 w-auto object-contain">
                        @endif
                        <span class="text-white text-xl font-black tracking-tight flex items-center">
                            {{ $global_site_name ?? 'PPOBKu' }}
                        </span>
                    </a>
                    
                    <nav class="hidden lg:flex space-x-6 text-xs font-bold text-gray-300">
                        <a href="{{ route('front.index') }}" class="text-white hover:text-up-yellow transition"><i class="fas fa-home mr-1"></i> Beranda</a>
                        <a href="{{ route('front.cek-pesanan') }}" class="hover:text-white transition"><i class="fas fa-search-dollar mr-1"></i> Cek Pesanan</a>
                        <a href="{{ route('front.page', 'daftar-harga') }}" class="hover:text-white transition"><i class="fas fa-list-alt mr-1"></i> Daftar Harga</a>
                        <a href="{{ route('front.article.index') }}" class="hover:text-white transition"><i class="fas fa-fire mr-1"></i> Promo & Acara</a>
                        
                        <!-- Dropdown Lainnya -->
                        <div class="relative group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="hover:text-white transition flex items-center focus:outline-none">
                                <i class="fas fa-ellipsis-h mr-1"></i> Lainnya <i class="fas fa-caret-down ml-1 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <!-- Dropdown Menu -->
                            <div x-show="open" x-transition.opacity class="absolute top-full left-0 mt-4 w-48 bg-up-card border border-up-border rounded-lg shadow-xl py-2 z-50">
                                <a href="{{ route('front.page', 'kontak') }}" class="block px-4 py-2 text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-headset w-5"></i> Hubungi CS</a>
                                <a href="{{ route('front.page', 'syarat-ketentuan') }}" class="block px-4 py-2 text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-book-open w-5"></i> Syarat & Ketentuan</a>
                                <a href="{{ route('front.page', 'kebijakan-privasi') }}" class="block px-4 py-2 text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-shield-alt w-5"></i> Kebijakan Privasi</a>
                            </div>
                        </div>
                    </nav>
                </div>

                <!-- Right: Search & Login -->
                <div class="flex items-center space-x-4">
                    <form action="{{ route('front.index') }}" method="GET" class="hidden md:block relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk, kategori, atau kode" class="bg-[#191d2c] border border-up-border text-white text-xs rounded-full pl-8 pr-4 py-2 w-64 focus:outline-none focus:border-up-yellow transition">
                    </form>
                    <a href="{{ route('login') }}" class="bg-up-yellow hover:bg-up-yellowhover text-black text-xs font-bold px-6 py-2 rounded shadow-sm transition">
                        MASUK
                    </a>
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
    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="fixed bottom-6 right-6 w-14 h-14 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg hover:scale-110 transition z-50">
        <i class="fab fa-whatsapp text-3xl"></i>
    </a>

    @stack('scripts')
</body>
</html>