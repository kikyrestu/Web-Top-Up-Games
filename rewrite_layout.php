<?php
$layoutHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ $global_site_name ?? 'UniPin Clone' }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        up: {
                            darkest: '#0c0f17', /* Topbar */
                            nav: '#1d2235',     /* Navbar bg */
                            body: '#111620',    /* Main background */
                            card: '#242a40',    /* Card background */
                            border: '#343b54',  /* Border line */
                            textmuted: '#8a94ad',
                            yellow: '#f49e0b',  /* Buttons/Highlight */
                            yellowhover: '#d98b08',
                        }
                    }
                }
            }
        }
    </script>
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
            <span class="flex items-center"><img src="https://flagcdn.com/w20/id.png" class="w-4 h-3 mr-1"> Indonesia - IDR</span>
        </div>
    </div>

    <!-- Main Navbar -->
    <header class="bg-up-nav sticky top-0 z-50 shadow-md border-b border-up-border/50">
        <div class="max-w-[1280px] mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                
                <!-- Left: Logo & Nav Links -->
                <div class="flex items-center flex-1">
                    <a href="{{ route('front.index') }}" class="mr-8 flex items-center">
                        <span class="text-white text-2xl font-black tracking-tight flex items-center">
                            Uni<span class="text-white bg-up-yellow rounded px-1 ml-[1px] text-[20px] font-bold">Pin</span>
                        </span>
                    </a>
                    
                    <nav class="hidden lg:flex space-x-6 text-xs font-bold text-gray-300">
                        <a href="#" class="text-white hover:text-up-yellow transition">Game</a>
                        <a href="#" class="hover:text-white transition">Promo dan Acara</a>
                        <a href="#" class="hover:text-white transition">Penukaran Poin</a>
                        <a href="#" class="hover:text-white transition">Keanggotaan</a>
                        <a href="#" class="hover:text-white transition flex items-center">Lainnya <i class="fas fa-caret-down ml-1"></i></a>
                    </nav>
                </div>

                <!-- Right: Search & Login -->
                <div class="flex items-center space-x-4">
                    <form action="{{ route('front.index') }}" method="GET" class="hidden md:block relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari di UniPin" class="bg-[#191d2c] border border-up-border text-white text-xs rounded-full pl-8 pr-4 py-2 w-64 focus:outline-none focus:border-up-yellow transition">
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
                    <button class="border border-up-border text-white text-sm px-6 py-2 rounded hover:bg-up-card transition">Lihat Semua</button>
                    
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
                        <li><a href="#" class="hover:text-white transition">Game</a></li>
                        <li><a href="#" class="hover:text-white transition">Voucher</a></li>
                        <li><a href="#" class="hover:text-white transition">SEACA eSports & Community</a></li>
                        <li><a href="#" class="hover:text-white transition">Saluran Pembayaran</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white text-sm font-bold mb-6">Informasi dan Dukungan</h4>
                    <ul class="text-up-textmuted text-xs space-y-3">
                        <li><a href="#" class="hover:text-white transition">UP Station Media</a></li>
                        <li><a href="#" class="hover:text-white transition">Promo dan Acara</a></li>
                        <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                        <li><a href="#" class="hover:text-white transition">Dukungan Pelanggan</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-up-border pt-6 flex flex-col md:flex-row justify-between items-center text-[10px] text-up-textmuted">
                <p>&copy; {{ date('Y') }} UniPin Clone. Semua Hak Cipta | <span class="text-up-yellow">Terms & Conditions</span> | <span class="text-up-yellow">Privacy Policy</span></p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp -->
    <a href="#" class="fixed bottom-6 right-6 w-14 h-14 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg hover:scale-110 transition z-50">
        <i class="fab fa-whatsapp text-3xl"></i>
    </a>
</body>
</html>
HTML;
file_put_contents('resources/views/layouts/front.blade.php', $layoutHtml);
echo "LAYOUT_UPDATED";
?>
