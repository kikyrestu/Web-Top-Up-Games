<?php
$content = file_get_contents('resources/views/layouts/front.blade.php');

$oldNav = <<<'HTML'
                    <nav class="hidden lg:flex space-x-6 text-xs font-bold text-gray-300">
                        <a href="#" class="text-white hover:text-up-yellow transition">Game</a>
                        <a href="#" class="hover:text-white transition">Promo dan Acara</a>
                        <a href="#" class="hover:text-white transition">Penukaran Poin</a>
                        <a href="#" class="hover:text-white transition">Keanggotaan</a>
                        <a href="#" class="hover:text-white transition flex items-center">Lainnya <i class="fas fa-caret-down ml-1"></i></a>
                    </nav>
HTML;

$newNav = <<<'HTML'
                    <nav class="hidden lg:flex space-x-6 text-xs font-bold text-gray-300">
                        <a href="{{ route('front.index') }}" class="text-white hover:text-up-yellow transition"><i class="fas fa-home mr-1"></i> Beranda</a>
                        <a href="{{ route('front.cek-pesanan') }}" class="hover:text-white transition"><i class="fas fa-search-dollar mr-1"></i> Cek Pesanan</a>
                        <a href="#" class="hover:text-white transition"><i class="fas fa-list-alt mr-1"></i> Daftar Harga</a>
                        <a href="{{ route('front.article.index') }}" class="hover:text-white transition"><i class="fas fa-fire mr-1"></i> Promo & Acara</a>
                        
                        <!-- Dropdown Lainnya -->
                        <div class="relative group" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="hover:text-white transition flex items-center focus:outline-none">
                                <i class="fas fa-ellipsis-h mr-1"></i> Lainnya <i class="fas fa-caret-down ml-1 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <!-- Dropdown Menu -->
                            <div x-show="open" x-transition.opacity class="absolute top-full left-0 mt-4 w-48 bg-up-card border border-up-border rounded-lg shadow-xl py-2 z-50">
                                <a href="#" class="block px-4 py-2 text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-headset w-5"></i> Hubungi CS</a>
                                <a href="#" class="block px-4 py-2 text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-book-open w-5"></i> Syarat & Ketentuan</a>
                                <a href="#" class="block px-4 py-2 text-gray-300 hover:text-up-yellow hover:bg-black/20 transition"><i class="fas fa-shield-alt w-5"></i> Kebijakan Privasi</a>
                            </div>
                        </div>
                    </nav>
HTML;

if (strpos($content, 'Cek Pesanan') === false) {
    $content = str_replace($oldNav, $newNav, $content);
    file_put_contents('resources/views/layouts/front.blade.php', $content);
    echo "INJECTED_NAVBAR";
} else {
    echo "ALREADY_EXISTS_NAVBAR";
}
?>
