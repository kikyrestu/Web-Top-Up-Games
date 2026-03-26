<?php
$content = file_get_contents('resources/views/front/index.blade.php');

$tokopediaWidgetHtml = <<<'HTML'
    <!-- Kategori & PPOB Widget (Tokopedia Style) -->
    <div class="mt-8 bg-white rounded-xl p-5 md:p-6 mb-10 shadow-[0_4px_12px_rgba(0,0,0,0.15)] text-gray-800 border border-gray-200">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            
            <!-- Left: Banner Promo -->
            <div class="xl:col-span-5 flex flex-col">
                <h2 class="text-xl font-bold mb-4 text-gray-900">Kategori Populer</h2>
                <div class="bg-[#00aa5b] rounded-xl flex-grow p-5 relative overflow-hidden flex flex-col justify-center min-h-[140px] shadow-sm">
                    <h3 class="text-white font-bold text-xl mb-1 relative z-10">Beli Pulsa & Tagihan</h3>
                    <p class="text-white/90 text-sm mb-4 relative z-10">Lebih cepat, mudah, dan banyak promo!</p>
                    <button class="bg-white text-[#00aa5b] rounded-full text-sm font-bold px-6 py-2 w-max relative z-10 hover:bg-gray-100 shadow transition">Cek Sekarang</button>
                    <!-- Background Decor -->
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                    <i class="fas fa-wallet absolute -right-2 -bottom-2 text-[80px] text-white/20 transform -rotate-12"></i>
                </div>
            </div>

            <!-- Right: PPOB Tabs Form -->
            <div class="xl:col-span-7 flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Top Up & Tagihan</h2>
                    <a href="#" class="text-[#00aa5b] text-sm font-bold hover:underline">Lihat Semua</a>
                </div>
                
                <div class="border border-gray-300 rounded-xl bg-white max-w-full overflow-hidden flex-grow flex flex-col" x-data="{ tab: 'pulsa', phone: '' }">
                    <!-- Tabs Header -->
                    <div class="flex border-b border-gray-300 overflow-x-auto hide-scroll bg-gray-50/50">
                        <button @click="tab = 'pulsa'" :class="tab === 'pulsa' ? 'text-[#00aa5b] border-b-[3px] border-[#00aa5b] font-bold bg-white' : 'text-gray-600 hover:text-gray-800 font-medium'" class="px-6 py-3.5 text-sm whitespace-nowrap transition-colors flex-grow md:flex-grow-0 text-center">Pulsa</button>
                        <button @click="tab = 'data'" :class="tab === 'data' ? 'text-[#00aa5b] border-b-[3px] border-[#00aa5b] font-bold bg-white' : 'text-gray-600 hover:text-gray-800 font-medium'" class="px-6 py-3.5 text-sm whitespace-nowrap transition-colors flex-grow md:flex-grow-0 text-center">Paket Data</button>
                        <button @click="tab = 'pln'" :class="tab === 'pln' ? 'text-[#00aa5b] border-b-[3px] border-[#00aa5b] font-bold bg-white' : 'text-gray-600 hover:text-gray-800 font-medium'" class="px-6 py-3.5 text-sm whitespace-nowrap transition-colors flex-grow md:flex-grow-0 text-center">Listrik PLN</button>
                        <button @click="tab = 'roaming'" :class="tab === 'roaming' ? 'text-[#00aa5b] border-b-[3px] border-[#00aa5b] font-bold bg-white' : 'text-gray-600 hover:text-gray-800 font-medium'" class="px-6 py-3.5 text-sm whitespace-nowrap transition-colors flex-grow md:flex-grow-0 text-center">Roaming</button>
                        <button class="px-4 py-3.5 text-gray-500 hover:text-gray-800 transition-colors border-l border-gray-200">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>

                    <!-- Input Fields -->
                    <div class="p-5 grid grid-cols-1 md:grid-cols-12 gap-5 items-end flex-grow bg-white">
                        <div class="md:col-span-5">
                            <label class="block text-sm text-gray-600 font-semibold mb-1.5" x-text="tab === 'pln' ? 'Nomor Meter / ID Pel' : 'Nomor Telepon'"></label>
                            <input type="text" x-model="phone" placeholder="Masukkan Nomor" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-900 focus:border-[#00aa5b] focus:ring-1 focus:ring-[#00aa5b] outline-none transition transition-shadow">
                        </div>
                        <div class="md:col-span-5">
                            <label class="block text-sm text-gray-600 font-semibold mb-1.5">Nominal</label>
                            <select class="w-full border text-gray-500 border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:border-[#00aa5b] focus:ring-1 focus:ring-[#00aa5b] outline-none bg-white transition shadow-sm appearance-none" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23131313%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 15px top 50%; background-size: 10px auto;">
                                <option>Pilih Nominal</option>
                                <option>Rp 10.000</option>
                                <option>Rp 20.000</option>
                                <option>Rp 50.000</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <button :class="phone.length > 4 ? 'bg-gray-300 text-gray-600 font-bold hover:bg-gray-400' : 'bg-gray-200 text-gray-400 font-bold cursor-not-allowed'" class="w-full py-2.5 rounded-lg text-sm transition">Beli</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Pills Bottom Row -->
        <div class="mt-8 flex items-center space-x-3 overflow-x-auto hide-scroll pb-2">
            <button class="flex items-center space-x-2 border border-gray-300 rounded-2xl px-4 py-2 text-sm font-medium text-gray-700 hover:border-[#00aa5b] hover:text-[#00aa5b] transition whitespace-nowrap bg-white shadow-sm">
                <i class="fas fa-th-large text-gray-500"></i> <span>Kategori</span>
            </button>
            <button class="flex items-center space-x-2 border border-gray-300 rounded-2xl px-4 py-2 text-sm font-medium text-gray-700 hover:border-[#00aa5b] hover:text-[#00aa5b] transition whitespace-nowrap bg-white shadow-sm">
                <span class="text-blue-500 text-lg">??</span> <span>Handphone & Tablet</span>
            </button>
            <button class="flex items-center space-x-2 border border-gray-300 rounded-2xl px-4 py-2 text-sm font-medium text-gray-700 hover:border-[#00aa5b] hover:text-[#00aa5b] transition whitespace-nowrap bg-white shadow-sm">
                <span class="text-blue-400 text-lg">??</span> <span>Top-Up & Tagihan</span>
            </button>
            <button class="flex items-center space-x-2 border border-gray-300 rounded-2xl px-4 py-2 text-sm font-medium text-gray-700 hover:border-[#00aa5b] hover:text-[#00aa5b] transition whitespace-nowrap bg-white shadow-sm">
                <span class="text-red-500 text-lg">??</span> <span>Elektronik</span>
            </button>
            <button class="flex items-center space-x-2 border border-gray-300 rounded-2xl px-4 py-2 text-sm font-medium text-gray-700 hover:border-[#00aa5b] hover:text-[#00aa5b] transition whitespace-nowrap bg-white shadow-sm">
                <span class="text-yellow-600 text-lg">??</span> <span>Perawatan Hewan</span>
            </button>
            <button class="flex items-center space-x-2 border border-gray-300 rounded-2xl px-4 py-2 text-sm font-medium text-gray-700 hover:border-[#00aa5b] hover:text-[#00aa5b] transition whitespace-nowrap bg-white shadow-sm">
                <span class="text-yellow-500 text-lg">??</span> <span>Keuangan</span>
            </button>
            <button class="flex items-center space-x-2 border border-gray-300 rounded-2xl px-4 py-2 text-sm font-medium text-gray-700 hover:border-[#00aa5b] hover:text-[#00aa5b] transition whitespace-nowrap bg-white shadow-sm">
                <span class="text-green-500 text-lg">??</span> <span>Komputer & Laptop</span>
            </button>
        </div>
    </div>
HTML;

if (strpos($content, '<!-- Kategori & PPOB Widget (Tokopedia Style) -->') === false) {
    $content = str_replace('<!-- Sections Generator (Populer, Seluler, PC) -->', $tokopediaWidgetHtml . "\n    " . '<!-- Sections Generator (Populer, Seluler, PC) -->', $content);
    file_put_contents('resources/views/front/index.blade.php', $content);
    echo "INJECTED_TOKO_WIDGET";
} else {
    echo "ALREADY_EXISTS_TOKO_WIDGET";
}
?>
