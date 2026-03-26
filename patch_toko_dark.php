<?php
$content = file_get_contents('resources/views/front/index.blade.php');

// Define the old start and end markers to replace
$startMarker = '<!-- Kategori & PPOB Widget (Tokopedia Style) -->';
$endMarker = '<!-- Sections Generator (Populer, Seluler, PC) -->';

// Find offsets
$startPos = strpos($content, $startMarker);
$endPos = strpos($content, $endMarker);

if ($startPos !== false && $endPos !== false) {
    
    $newWidgetHtml = <<<'HTML'
    <!-- Kategori & PPOB Widget (Tokopedia Style) -->
    <div class="mt-8 bg-[#161a29] rounded-xl p-5 md:p-6 mb-10 shadow-lg text-white border border-up-border">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            
            <!-- Left: Banner Promo -->
            <div class="xl:col-span-5 flex flex-col">
                <h2 class="text-xl font-bold mb-4 text-white">Kategori Populer</h2>
                <div class="bg-gradient-to-br from-up-yellow to-[#ab6500] rounded-xl flex-grow p-5 relative overflow-hidden flex flex-col justify-center min-h-[140px] shadow-sm">
                    <h3 class="text-black font-extrabold text-xl mb-1 relative z-10">Beli Pulsa & Tagihan</h3>
                    <p class="text-black/80 font-medium text-sm mb-4 relative z-10">Lebih cepat, mudah, dan banyak promo!</p>
                    <button class="bg-[#11131c] text-white rounded-full text-sm font-bold px-6 py-2 w-max relative z-10 hover:bg-black shadow transition border border-gray-700">Cek Sekarang</button>
                    <!-- Background Decor -->
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/20 rounded-full blur-xl"></div>
                    <i class="fas fa-bolt absolute -right-2 -bottom-2 text-[80px] text-black/10 transform -rotate-12"></i>
                </div>
            </div>

            <!-- Right: PPOB Tabs Form -->
            <div class="xl:col-span-7 flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-white">Top Up & Tagihan</h2>
                    <a href="#" class="text-up-yellow text-sm font-bold hover:text-up-yellowhover transition">Lihat Semua</a>
                </div>
                
                <div class="border border-up-border rounded-xl bg-up-card max-w-full overflow-hidden flex-grow flex flex-col" x-data="{ tab: 'pulsa', phone: '' }">
                    <!-- Tabs Header -->
                    <div class="flex border-b border-up-border overflow-x-auto hide-scroll bg-[#11131c]">
                        <button @click="tab = 'pulsa'" :class="tab === 'pulsa' ? 'text-up-yellow border-b-[3px] border-up-yellow font-bold bg-up-card' : 'text-gray-400 hover:text-gray-200 font-medium'" class="px-6 py-3.5 text-sm whitespace-nowrap transition-colors flex-grow md:flex-grow-0 text-center">Pulsa</button>
                        <button @click="tab = 'data'" :class="tab === 'data' ? 'text-up-yellow border-b-[3px] border-up-yellow font-bold bg-up-card' : 'text-gray-400 hover:text-gray-200 font-medium'" class="px-6 py-3.5 text-sm whitespace-nowrap transition-colors flex-grow md:flex-grow-0 text-center">Paket Data</button>
                        <button @click="tab = 'pln'" :class="tab === 'pln' ? 'text-up-yellow border-b-[3px] border-up-yellow font-bold bg-up-card' : 'text-gray-400 hover:text-gray-200 font-medium'" class="px-6 py-3.5 text-sm whitespace-nowrap transition-colors flex-grow md:flex-grow-0 text-center">Listrik PLN</button>
                        <button @click="tab = 'roaming'" :class="tab === 'roaming' ? 'text-up-yellow border-b-[3px] border-up-yellow font-bold bg-up-card' : 'text-gray-400 hover:text-gray-200 font-medium'" class="px-6 py-3.5 text-sm whitespace-nowrap transition-colors flex-grow md:flex-grow-0 text-center">Roaming</button>
                        <button class="px-4 py-3.5 text-gray-500 hover:text-gray-300 transition-colors border-l border-up-border">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>

                    <!-- Input Fields -->
                    <div class="p-5 grid grid-cols-1 md:grid-cols-12 gap-5 items-end flex-grow bg-up-card">
                        <div class="md:col-span-5">
                            <label class="block text-sm text-gray-300 font-semibold mb-1.5" x-text="tab === 'pln' ? 'Nomor Meter / ID Pel' : 'Nomor Telepon'"></label>
                            <input type="text" x-model="phone" placeholder="Masukkan Nomor" class="w-full bg-[#111620] border border-up-border rounded-lg px-3 py-2.5 text-sm text-white focus:border-up-yellow focus:ring-1 focus:ring-up-yellow outline-none transition transition-shadow placeholder-gray-600">
                        </div>
                        <div class="md:col-span-5">
                            <label class="block text-sm text-gray-300 font-semibold mb-1.5">Nominal</label>
                            <select class="w-full bg-[#111620] text-gray-300 border border-up-border rounded-lg px-3 py-2.5 text-sm focus:border-up-yellow focus:ring-1 focus:ring-up-yellow outline-none transition shadow-sm appearance-none" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%238a94ad%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 15px top 50%; background-size: 10px auto;">
                                <option>Pilih Nominal</option>
                                <option>Rp 10.000</option>
                                <option>Rp 20.000</option>
                                <option>Rp 50.000</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <button :class="phone.length > 4 ? 'bg-up-yellow text-[#111] font-bold hover:bg-up-yellowhover' : 'bg-[#343b54] text-gray-500 font-bold cursor-not-allowed'" class="w-full py-2.5 rounded-lg text-sm transition">Beli</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Pills Bottom Row -->
        <div class="mt-8 flex items-center space-x-3 overflow-x-auto hide-scroll pb-2">
            <button class="flex items-center space-x-2 border border-up-border bg-up-card rounded-2xl px-4 py-2 text-sm font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition whitespace-nowrap shadow-sm">
                <i class="fas fa-th-large text-gray-400"></i> <span>Kategori</span>
            </button>
            <button class="flex items-center space-x-2 border border-up-border bg-up-card rounded-2xl px-4 py-2 text-sm font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition whitespace-nowrap shadow-sm">
                <span class="text-up-yellow text-lg"><i class="fas fa-mobile-alt"></i></span> <span>Handphone & Tablet</span>
            </button>
            <button class="flex items-center space-x-2 border border-up-border bg-up-card rounded-2xl px-4 py-2 text-sm font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition whitespace-nowrap shadow-sm">
                <span class="text-up-yellow text-lg"><i class="fas fa-file-invoice-dollar"></i></span> <span>Top-Up & Tagihan</span>
            </button>
            <button class="flex items-center space-x-2 border border-up-border bg-up-card rounded-2xl px-4 py-2 text-sm font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition whitespace-nowrap shadow-sm">
                <span class="text-up-yellow text-lg"><i class="fas fa-headphones"></i></span> <span>Elektronik</span>
            </button>
            <button class="flex items-center space-x-2 border border-up-border bg-up-card rounded-2xl px-4 py-2 text-sm font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition whitespace-nowrap shadow-sm">
                <span class="text-up-yellow text-lg"><i class="fas fa-paw"></i></span> <span>Perawatan Hewan</span>
            </button>
            <button class="flex items-center space-x-2 border border-up-border bg-up-card rounded-2xl px-4 py-2 text-sm font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition whitespace-nowrap shadow-sm">
                <span class="text-up-yellow text-lg"><i class="fas fa-wallet"></i></span> <span>Keuangan</span>
            </button>
            <button class="flex items-center space-x-2 border border-up-border bg-up-card rounded-2xl px-4 py-2 text-sm font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition whitespace-nowrap shadow-sm">
                <span class="text-up-yellow text-lg"><i class="fas fa-laptop"></i></span> <span>Komputer & Laptop</span>
            </button>
        </div>
    </div>
        
    <!-- Sections Generator (Populer, Seluler, PC) -->
HTML;

    // Slice the old block entirely out and insert new colored block
    $firstHalf = substr($content, 0, $startPos);
    $secondHalf = substr($content, $endPos + strlen($endMarker));
    
    $finalContent = $firstHalf . $newWidgetHtml . "\n" . $secondHalf;
    file_put_contents('resources/views/front/index.blade.php', $finalContent);
    echo "INJECTED_DARK_TOKO";
} else {
    echo "FAIL_POS";
}
?>
