<?php
$content = file_get_contents('resources/views/front/index.blade.php');
$target = '<!-- Games Populer -->';

$flashSaleHtml = <<<'HTML'
    <!-- Flash Sale / Hot Deals -->
    @if($flashSaleProducts->count() > 0)
    <div class="mb-12 relative overflow-hidden bg-card rounded-2xl border border-gray-800 p-6 md:p-8 mt-6">
        <div class="absolute -right-20 -top-20 opacity-10 w-96 h-96 bg-primary rounded-full blur-3xl mix-blend-screen"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div class="flex items-center">
                <div class="text-4xl text-primary animate-pulse mr-4"><i class="fas fa-bolt"></i></div>
                <div>
                    <h2 class="text-3xl font-black text-white italic tracking-wider uppercase">Flash Sale</h2>
                    <p class="text-gray-400 font-semibold mt-1">Sikat mumpung murah, stok terbatas!</p>
                </div>
            </div>
            
            <!-- Countdown Timer -->
            <div class="mt-4 md:mt-0 flex space-x-2 lg:space-x-3 items-center" 
                x-data="countdown()" x-init="start()">
                <div class="flex flex-col items-center">
                    <div class="bg-primary/20 text-primary border border-primary/50 font-black text-xl md:text-2xl w-12 h-12 md:w-16 md:h-16 flex items-center justify-center rounded-xl shadow-[0_0_15px_rgba(249,115,22,0.3)]">
                        <span x-text="hours">00</span>
                    </div>
                </div>
                <span class="text-primary font-bold text-xl">:</span>
                <div class="flex flex-col items-center">
                    <div class="bg-primary/20 text-primary border border-primary/50 font-black text-xl md:text-2xl w-12 h-12 md:w-16 md:h-16 flex items-center justify-center rounded-xl shadow-[0_0_15px_rgba(249,115,22,0.3)]">
                        <span x-text="minutes">00</span>
                    </div>
                </div>
                <span class="text-primary font-bold text-xl">:</span>
                <div class="flex flex-col items-center">
                    <div class="bg-primary/20 text-primary border border-primary/50 font-black text-xl md:text-2xl w-12 h-12 md:w-16 md:h-16 flex items-center justify-center rounded-xl shadow-[0_0_15px_rgba(249,115,22,0.3)]">
                        <span x-text="seconds">00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 lg:gap-6 relative z-10">
            @foreach($flashSaleProducts as $product)
            <div class="bg-gray-900 border border-gray-700 hover:border-primary rounded-2xl p-4 transition group relative overflow-hidden hover:-translate-y-1 shadow-md cursor-pointer">
                <div class="absolute top-0 right-0 bg-red-600 text-white text-[10px] font-black px-2 py-1 rounded-bl-lg uppercase tracking-wider shadow-lg z-10">
                    -{{ number_format((($product->price - $product->price_discount) / $product->price) * 100, 0) }}%
                </div>
                
                <h3 class="text-gray-300 font-bold text-sm mb-1 truncate">{{ $product->name }}</h3>
                <div class="flex items-center space-x-2 text-xs mb-3">
                    <span class="text-gray-500 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                </div>
                <div class="text-primary font-black text-lg">Rp {{ number_format($product->price_discount, 0, ',', '.') }}</div>
                
                <div class="mt-4 bg-gray-800 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-primary to-yellow-500 w-2/3 h-full rounded-full"></div>
                </div>
                <div class="text-[10px] text-gray-500 mt-1 flex justify-between">
                    <span>Tersisa 15</span>
                    <span>Terjual 35</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Alpine JS Script for countdown -->
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('countdown', () => ({
            hours: '12',
            minutes: '00',
            seconds: '00',
            
            start() {
                var countDownDate = new Date().getTime() + (12 * 60 * 60 * 1000);
                
                setInterval(() => {
                    var now = new Date().getTime();
                    var distance = countDownDate - now;
                    
                    this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                    this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                    this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
                    
                    if (distance < 0) {
                        this.hours = "00";
                        this.minutes = "00";
                        this.seconds = "00";
                    }
                }, 1000);
            }
        }));
    });
    </script>
    @endif

HTML;

if (strpos($content, '<!-- Flash Sale / Hot Deals -->') === false) {
    $content = str_replace($target, $flashSaleHtml . "\n    " . $target, $content);
    file_put_contents('resources/views/front/index.blade.php', $content);
    echo "INJECTED";
} else {
    echo "ALREADY EXISTS";
}
?>
