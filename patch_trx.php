<?php
$content = file_get_contents('resources/views/front/index.blade.php');

$liveTransactionHtml = <<<'HTML'
    <!-- Live Transaction Widget -->
    <div class="mb-12">
        <div class="flex items-center mb-4">
            <h2 class="text-2xl font-black text-white tracking-wide uppercase italic"><i class="fas fa-satellite-dish text-primary mr-2"></i>Live Transaksi</h2>
            <div class="ml-3 flex space-x-1 items-center bg-red-600/20 px-2 py-1 rounded border border-red-600/50">
                <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                <span class="text-[10px] text-red-500 font-bold tracking-widest uppercase ml-1">Live</span>
            </div>
        </div>
        
        <!-- Marquee / Scrolling Container -->
        <div class="bg-[#0a0a0a] border-l-4 border-primary p-4 overflow-hidden relative shadow-[0_0_20px_rgba(249,115,22,0.15)] rounded-r-lg" style="mask-image: linear-gradient(to right, transparent, black 5%, black 95%, transparent);">
            <div class="flex space-x-4 animate-[marquee_20s_linear_infinite] w-max" x-data="{
                transactions: [
                    {user: '0812***891', game: 'Mobile Legends', item: '86 Diamonds', time: 'Baru saja'},
                    {user: '0857***112', game: 'Free Fire', item: '140 Diamonds', time: '1 mnt lalu'},
                    {user: 'Guest_12***', game: 'Valorant', item: '1050 VP', time: '2 mnt lalu'},
                    {user: '0819***334', game: 'PUBG Mobile', item: '325 UC', time: '3 mnt lalu'},
                    {user: '0821***992', game: 'Genshin Impact', item: '980 Genesis...', time: '4 mnt lalu'},
                    {user: 'Guest_99***', game: 'Token PLN', item: 'Rp 100.000', time: '5 mnt lalu'}
                ]
            }">
                <!-- Clone list to loop -->
                <template x-for="i in 3">
                    <div class="flex space-x-4">
                        <template x-for="trx in transactions">
                            <div class="flex items-center bg-black border border-gray-800 px-4 py-2 min-w-max transform skew-x-[-12deg] hover:border-primary transition duration-300">
                                <div class="transform skew-x-[12deg] flex items-center">
                                    <div class="w-8 h-8 rounded-none bg-primary/20 text-primary flex items-center justify-center mr-3 border border-primary/30">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider"><span x-text="trx.user"></span> <span class="mx-1 text-gray-600">?</span> <span class="text-primary" x-text="trx.time"></span></div>
                                        <div class="text-sm text-white font-black italic"><span x-text="trx.game"></span> <span class="text-gray-500 mx-1">-</span> <span class="text-yellow-400" x-text="trx.item"></span></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Artikel Section -->
HTML;

if (strpos($content, '<!-- Live Transaction Widget -->') === false) {
    $content = str_replace('<!-- Artikel Section -->', $liveTransactionHtml, $content);
    file_put_contents('resources/views/front/index.blade.php', $content);
    echo "INJECTED_LIVETRX";
} else {
    echo "ALREADY_EXISTS_LIVETRX";
}
?>
