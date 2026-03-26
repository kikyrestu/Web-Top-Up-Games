@extends('layouts.front')
@section('title', 'Artikel, Promo, dan Berita Game')
@section('meta_description', 'Baca artikel, promo, dan berita terbaru seputar top up game, tips bermain, serta update event menarik.')
@section('canonical', route('front.article.index'))

@push('jsonld')
<script type="application/ld+json">
{
    "{{ '@' }}context": "https://schema.org",
    "{{ '@' }}type": "WebPage",
    "name": "Artikel, Promo, dan Berita Game",
    "url": "{{ route('front.article.index') }}",
    "description": "Baca artikel, promo, dan berita terbaru seputar top up game."
}
</script>
@endpush

@section('content')

<!-- Hero Section -->
<section class="relative pt-32 pb-20 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-[#1c1c1c] via-[#1c1c1c] to-[#2a1b15] z-0"></div>
    <div class="absolute top-0 right-0 w-64 h-64 bg-[#f97316]/10 rounded-full blur-[100px]"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#f97316]/5 rounded-full blur-[80px]"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">
            Berita & <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#f97316] to-[#ff983f]">Artikel</span>
        </h1>
        <p class="text-gray-400 max-w-2xl mx-auto text-lg">
            Dapatkan informasi terbaru seputar game, tips & trik, hingga promo menarik dari kami.
        </p>
    </div>
</section>

<!-- Articles Loop -->
<section class="py-16 bg-[#121212] min-h-screen">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        @if($articles->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($articles as $article)
                <div class="bg-[#1c1c1c] rounded-2xl overflow-hidden border border-gray-800 hover:border-[#f97316]/50 transition-all duration-300 group hover:-translate-y-1 hover:shadow-[0_10px_30px_rgba(249,115,22,0.1)] flex flex-col h-full">
                    <div class="relative overflow-hidden aspect-video">
                        @if($article->image)
                            <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-gray-800 flex items-center justify-center text-gray-500">
                                <i class="fas fa-image text-4xl"></i>
                            </div>
                        @endif
                        <div class="absolute top-4 left-4">
                            <span class="bg-[#f97316] text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">Info</span>
                        </div>
                    </div>

                    <div class="p-6 flex flex-col flex-grow">
                        <div class="flex items-center text-gray-400 text-xs mb-3 space-x-4">
                            <span class="flex items-center"><i class="far fa-calendar-alt mr-1.5"></i> {{ $article->created_at->format('d M Y') }}</span>
                        </div>

                        <a href="{{ route('front.article.show', $article->slug) }}">
                            <h3 class="text-xl font-bold text-white mb-3 line-clamp-2 group-hover:text-[#f97316] transition-colors leading-snug">
                                {{ $article->title }}
                            </h3>
                        </a>

                        <p class="text-gray-400 text-sm line-clamp-3 mb-5 flex-grow">
                            {{ Str::limit(strip_tags($article->content), 120) }}
                        </p>

                        <div class="pt-4 border-t border-gray-800 mt-auto">
                            <a href="{{ route('front.article.show', $article->slug) }}" class="inline-flex items-center text-[#f97316] hover:text-[#ff983f] font-semibold text-sm transition-colors group/link">
                                Baca Selengkapnya
                                <i class="fas fa-arrow-right ml-2 group-hover/link:translate-x-1 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-16 flex justify-center">
                {{ $articles->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-[#1c1c1c] rounded-2xl border border-gray-800">
                <div class="w-24 h-24 bg-gray-800/50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-newspaper text-4xl text-gray-500"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Belum ada Artikel</h3>
                <p class="text-gray-400">Silakan kembali lagi nanti untuk update terbaru.</p>
            </div>
        @endif
    </div>
</section>

@endsection