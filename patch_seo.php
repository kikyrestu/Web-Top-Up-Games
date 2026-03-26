<?php
$content = file_get_contents('resources/views/front/index.blade.php');

$seoHtml = <<<'HTML'
    <!-- SEO & About Section -->
    <div class="mt-16 mb-8 bg-black border-2 border-gray-800 p-6 md:p-10 rounded-none relative overflow-hidden group hover:border-primary transition duration-500">
        <div class="absolute -right-20 -bottom-20 opacity-5 w-64 h-64 bg-primary rounded-full blur-3xl group-hover:opacity-20 transition duration-500"></div>
        <h1 class="text-2xl font-black text-white italic uppercase tracking-wider mb-4 border-l-4 border-primary pl-4 transform skew-x-[-8deg]"><span class="transform skew-x-[8deg] block">Tempat Top Up Game termurah SE-INDONESIA!</span></h1>
        
        <div class="text-gray-400 text-sm space-y-4 leading-relaxed font-medium">
            <p>
                <strong>PPOBKu</strong> adalah platform layanan Top Up Game, Beli Voucher, Beli Pulsa, dan Token PLN paling brutal dengan harga termurah se-Indonesia. Proses instan kilat cuma butuh hitungan detik, saldo langsung masuk!
            </p>
            <p>
                Bosan dengan layanan top up yang lelet? Di sini tempatnya para gamers pro seperti player <strong>Mobile Legends (ML), Free Fire (FF), PUBG Mobile, Valorant, dan Genshin Impact</strong> untuk memenuhi kebutuhan gaming mereka. Tersedia berbagai metode pembayaran paling lengkap mulai dari BCA, Mandiri, BNI, BRI, OVO, DANA, GoPay, E-Wallet lainnya, dan QRIS.
            </p>
            <p>
                Sistem 100% otomatis, online 24 jam nonstop tanpa jam offline. Nikmati juga flash promo setiap harinya dengan diskon coret hingga 90% bikin ngiler. Jadi reseller kami juga bisa untuk mendulang cuan dari jualan diamond dan pulsa di tongkronganmu!
            </p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-6 border-t border-gray-800/50">
            <div class="flex items-center space-x-3 text-white">
                <i class="fas fa-bolt text-2xl text-primary"></i>
                <div class="text-xs font-bold uppercase">Proses<br>1 Detik</div>
            </div>
            <div class="flex items-center space-x-3 text-white">
                <i class="fas fa-tags text-2xl text-yellow-500"></i>
                <div class="text-xs font-bold uppercase">Promo<br>Harian</div>
            </div>
            <div class="flex items-center space-x-3 text-white">
                <i class="fas fa-headset text-2xl text-blue-500"></i>
                <div class="text-xs font-bold uppercase">CS Ready<br>24 Jam</div>
            </div>
            <div class="flex items-center space-x-3 text-white">
                <i class="fas fa-shield-alt text-2xl text-green-500"></i>
                <div class="text-xs font-bold uppercase">100% Aman<br>& Legal</div>
            </div>
        </div>
    </div>
</div>
@endsection
HTML;

if (strpos($content, '<!-- SEO & About Section -->') === false) {
    $content = str_replace("</div>\n@endsection", $seoHtml, $content);
    file_put_contents('resources/views/front/index.blade.php', $content);
    echo "INJECTED_SEO";
} else {
    echo "ALREADY_EXISTS_SEO";
}
?>
