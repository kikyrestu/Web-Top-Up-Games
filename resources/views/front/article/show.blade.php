@extends('layouts.front')
@section('title', $article->title)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($article->content), 160, '...'))
@section('canonical', route('front.article.show', $article->slug))
@if($article->image)
    @section('meta_image', asset('storage/' . $article->image))
@endif
@push('jsonld')
<script type="application/ld+json">
{
    "{{ '@' }}context": "https://schema.org",
    "{{ '@' }}type": "WebPage",
    "name": @json($article->title),
    "url": "{{ route('front.article.show', $article->slug) }}",
    "description": @json(\Illuminate\Support\Str::limit(strip_tags($article->content), 160, '...'))
}
</script>
<script type="application/ld+json">
{
    "{{ '@' }}context": "https://schema.org",
    "{{ '@' }}type": "Article",
    "headline": @json($article->title),
    "url": "{{ route('front.article.show', $article->slug) }}",
    "inLanguage": "id-ID",
    "isAccessibleForFree": true,
    "datePublished": "{{ $article->created_at->toIso8601String() }}",
    "dateModified": "{{ $article->updated_at->toIso8601String() }}",
    "mainEntityOfPage": "{{ route('front.article.show', $article->slug) }}",
    "image": ["{{ $article->image ? Storage::url($article->image) : (!empty($global_site_logo) ? asset('storage/' . $global_site_logo) : asset('favicon.ico')) }}"],
    "author": {
        "{{ '@' }}type": "Organization",
        "name": "{{ $global_site_name ?? 'PPOBKu' }}"
    },
    "publisher": {
        "{{ '@' }}type": "Organization",
        "name": "{{ $global_site_name ?? 'PPOBKu' }}",
        "logo": {
            "{{ '@' }}type": "ImageObject",
            "url": "{{ !empty($global_site_logo) ? asset('storage/' . $global_site_logo) : asset('favicon.ico') }}"
        }
    },
    "description": @json(\Illuminate\Support\Str::limit(strip_tags($article->content), 160, '...'))
}
</script>
<script type="application/ld+json">
{
    "{{ '@' }}context": "https://schema.org",
    "{{ '@' }}type": "BreadcrumbList",
    "itemListElement": [
        {
            "{{ '@' }}type": "ListItem",
            "position": 1,
            "name": "Beranda",
            "item": "{{ route('front.index') }}"
        },
        {
            "{{ '@' }}type": "ListItem",
            "position": 2,
            "name": "Artikel",
            "item": "{{ route('front.article.index') }}"
        },
        {
            "{{ '@' }}type": "ListItem",
            "position": 3,
            "name": @json($article->title),
            "item": "{{ route('front.article.show', $article->slug) }}"
        }
    ]
}
</script>
@endpush

@section('content')

<!-- Header Section -->
<section class="relative pt-32 pb-16 overflow-hidden">
    <!-- Background Magic -->
    <div class="absolute inset-0 bg-gradient-to-b from-[#1c1c1c] via-[#121212] to-[#121212] z-0"></div>
    <div class="absolute top-[-100px] right-[-100px] w-[500px] h-[500px] bg-[#f97316]/5 rounded-full blur-[150px]"></div>
    
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 max-w-4xl pt-6">
        <!-- Breadcrumb -->
        <nav class="flex mb-8 text-sm" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('front.index') }}" class="inline-flex items-center text-gray-400 hover:text-white transition-colors">
                        <i class="fas fa-home mr-2 text-[#f97316]"></i> Beranda
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-500 text-xs mx-2"></i>
                        <a href="{{ route('front.article.index') }}" class="text-gray-400 hover:text-white transition-colors">Artikel</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-500 text-xs mx-2"></i>
                        <span class="text-[#f97316] font-medium line-clamp-1 truncate max-w-[200px]">{{ $article->title }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Title & Meta -->
        <h1 class="text-4xl md:text-5xl font-extrabold text-white mb-6 leading-tight tracking-tight">
            {{ $article->title }}
        </h1>
        
        <div class="flex items-center space-x-6 text-sm text-gray-400 mb-8 border-b border-gray-800 pb-8">
            <div class="flex items-center bg-[#f97316]/10 text-[#f97316] px-4 py-2 rounded-full border border-[#f97316]/20">
                <i class="fas fa-calendar-alt mr-2"></i>
                <span class="font-medium">{{ $article->created_at->translatedFormat('d F Y') }}</span>
            </div>
            <div class="flex items-center hidden sm:flex">
                <i class="fas fa-tags mr-2 text-gray-500"></i> Informasi
            </div>
        </div>

        <!-- Featured Image -->
        @if($article->image)
        <div class="w-full relative rounded-3xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-gray-800 group">
            <img src="{{ Storage::url($article->image) }}" alt="{{ $article->title }}" loading="eager" fetchpriority="high" decoding="async" class="w-full h-auto max-h-[500px] object-cover bg-[#1c1c1c] transition-transform duration-700 hover:scale-105">
            <div class="absolute inset-0 ring-1 ring-inset ring-white/10 rounded-3xl pointer-events-none"></div>
        </div>
        @endif
    </div>
</section>

<!-- Content Engine -->
<section class="pb-24 bg-[#121212]">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
        <div class="bg-[#1c1c1c] rounded-3xl border border-gray-800 p-8 md:p-12 shadow-2xl relative">
            <div class="absolute -top-6 -left-6 text-[#f97316]/10 text-9xl">
                <i class="fas fa-quote-left"></i>
            </div>
            
            <!-- Main Content Markdown / HTML text-white/90 focus -->
            <article class="prose prose-invert prose-lg max-w-none text-gray-300 leading-relaxed 
                prose-headings:text-white prose-headings:font-bold prose-headings:mt-8 prose-headings:mb-6
                prose-a:text-[#f97316] prose-a:no-underline hover:prose-a:text-[#ff983f]
                prose-strong:text-white prose-strong:font-semibold
                prose-img:rounded-2xl prose-img:shadow-lg
                prose-blockquote:border-l-4 prose-blockquote:border-[#f97316] prose-blockquote:pl-6 prose-blockquote:text-gray-400 prose-blockquote:italic
                prose-li:marker:text-[#f97316] z-10 relative">
                
                {!! $article->content !!}
                
            </article>

            <!-- Share Block -->
            <div class="mt-14 pt-8 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between">
                <span class="text-white font-bold mb-4 sm:mb-0">Share Artikel ini:</span>
                <div class="flex space-x-3">
                    <a href="#" class="w-10 h-10 rounded-full bg-blue-600 hover:bg-blue-700 flex items-center justify-center text-white transition-all hover:-translate-y-1 shadow-lg">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-blue-400 hover:bg-blue-500 flex items-center justify-center text-white transition-all hover:-translate-y-1 shadow-lg">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-green-500 hover:bg-green-600 flex items-center justify-center text-white transition-all hover:-translate-y-1 shadow-lg">
                        <i class="fab fa-whatsapp text-lg"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-700 hover:bg-gray-600 flex items-center justify-center text-white transition-all hover:-translate-y-1 shadow-lg border border-gray-600">
                        <i class="fas fa-link"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Articles Hub -->
@if($relatedArticles && $relatedArticles->count() > 0)
<section class="py-20 bg-[#1c1c1c] border-t border-gray-800 relative overflow-hidden">
    <div class="absolute inset-0 bg-grid-white/[0.02] bg-[size:30px_30px]"></div>
    
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 max-w-6xl">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h3 class="text-3xl font-extrabold text-white mb-2 tracking-tight">
                    <span class="text-[#f97316]">Artikel</span> Terkait
                </h3>
                <p class="text-gray-400 text-lg">Baca update menarik lainnya.</p>
            </div>
            <a href="{{ route('front.article.index') }}" class="hidden sm:inline-flex items-center text-[#f97316] hover:text-white transition-colors bg-[#f97316]/10 px-6 py-2.5 rounded-full font-medium border border-[#f97316]/20 hover:border-[#f97316]/50">
                Lihat Semua Berita <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($relatedArticles as $related)
            <div class="bg-black/40 backdrop-blur-sm rounded-2xl overflow-hidden border border-gray-800 hover:border-[#f97316]/30 transition-all duration-300 group hover:-translate-y-1 flex flex-col h-full">
                <a href="{{ route('front.article.show', $related->slug) }}" class="block relative overflow-hidden aspect-video">
                    @if($related->image)
                        <img src="{{ Storage::url($related->image) }}" alt="{{ $related->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full bg-gray-800 flex items-center justify-center">
                            <i class="fas fa-image text-3xl text-gray-600"></i>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-4">
                        <span class="text-[#f97316] text-sm font-bold flex items-center">BACA <i class="fas fa-chevron-right ml-1 text-xs"></i></span>
                    </div>
                </a>
                
                <div class="p-5 flex flex-col flex-grow">
                    <div class="text-gray-500 text-xs mb-2 flex items-center">
                        <i class="far fa-clock mr-1.5"></i> {{ $related->created_at->diffForHumans() }}
                    </div>
                    <a href="{{ route('front.article.show', $related->slug) }}">
                        <h4 class="text-lg font-bold text-white mb-2 line-clamp-2 group-hover:text-[#f97316] transition-colors leading-snug">
                            {{ $related->title }}
                        </h4>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection