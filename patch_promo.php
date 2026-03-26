<?php
$content = file_get_contents('resources/views/front/index.blade.php');

$promoHtml = <<<'HTML'
    <!-- Promo dan Acara Section -->
    <div class="mt-4 bg-[#161a29] p-5 rounded-lg border border-up-border shadow-sm mb-12">
        <div class="flex justify-between items-end mb-5 border-b border-up-border pb-3">
            <div>
                <h2 class="text-white text-lg font-bold">Promo dan Acara</h2>
                <p class="text-up-textmuted text-xs mt-1">Berita dan panduan game terbaru di <a href="#" class="text-up-yellow font-semibold hover:underline">UP Station Media <i class="fas fa-external-link-square-alt ml-1"></i></a></p>
            </div>
            <a href="{{ route('front.article.index') }}" class="bg-[#343b54] text-gray-300 text-[10px] font-bold px-3 py-1.5 rounded hover:bg-gray-600 transition">Lainnya <i class="fas fa-caret-right ml-1"></i></a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4">
            @forelse($latestArticles as $article)
            <a href="{{ route('front.article.show', $article->slug) }}" class="block bg-up-card rounded overflow-hidden group border border-transparent hover:border-up-yellow transition-colors relative">
                <div class="aspect-[24/9] lg:aspect-[21/6] w-full bg-gray-800 relative overflow-hidden">
                    <img src="{{ $article->image ? asset('storage/'.$article->image) : 'https://ui-avatars.com/api/?name=Promo&background=1d2235&color=fff' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-4">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-up-textmuted text-[10px] font-bold tracking-widest uppercase">PROMO</span>
                        <span class="text-up-textmuted text-[10px]">{{ $article->created_at->format('d M Y') }}</span>
                    </div>
                    <h3 class="text-white text-sm font-bold line-clamp-2 leading-snug group-hover:text-up-yellow transition">
                        {{ $article->title }}
                    </h3>
                </div>
            </a>
            @empty
            <div class="col-span-full border border-dashed border-up-border rounded-lg text-center py-10 text-up-textmuted text-sm">
                Belum ada promo & acara saat ini.
            </div>
            @endforelse
        </div>
    </div>
HTML;

if (strpos($content, '<!-- Promo dan Acara Section -->') === false) {
    $content = str_replace("</div>\n@endsection", $promoHtml . "\n</div>\n@endsection", $content);
    file_put_contents('resources/views/front/index.blade.php', $content);
    echo "INJECTED_PROMO";
} else {
    echo "ALREADY_EXISTS_PROMO";
}
?>
