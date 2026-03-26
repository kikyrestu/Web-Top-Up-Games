@extends('layouts.front')
@section('title', 'Top Up Game Cepat & Murah')
@section('meta_description', 'Top up game, voucher, dan pembayaran PPOB cepat dengan harga kompetitif. Proses instan, aman, dan dukungan pelanggan responsif.')
@section('canonical', route('front.index'))
@if(!empty($searchQuery ?? null))
    @section('robots', 'noindex,follow')
@endif

@push('jsonld')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "{{ '@' }}type": "WebPage",
    "name": "Top Up Game Cepat & Murah",
    "url": "{{ route('front.index') }}",
    "description": "Top up game, voucher, dan pembayaran PPOB cepat dengan harga kompetitif."
}
</script>
@endpush

@section('content')
<div class="max-w-[1280px] mx-auto px-4 mt-6 space-y-8">

    @if(!empty($searchQuery))
    <section class="bg-[#161a29] p-5 rounded-xl border border-up-border shadow-lg">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-white text-lg font-bold">Hasil Pencarian</h2>
                <p class="text-gray-400 text-xs mt-0.5">Kata kunci: <span class="text-up-yellow font-semibold">{{ $searchQuery }}</span></p>
            </div>
            <a href="{{ route('front.index') }}" class="text-xs text-up-yellow font-semibold hover:text-up-yellowhover transition">Reset</a>
        </div>

        @if($searchCategories->isEmpty() && $searchProducts->isEmpty())
            <p class="text-sm text-gray-400">Tidak ada kategori atau produk yang cocok.</p>
        @else
            @if($searchCategories->isNotEmpty())
            <div class="mb-4">
                <h3 class="text-white text-sm font-semibold mb-2">Kategori</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($searchCategories as $category)
                    <a href="{{ route('front.category', $category->slug ?? $category->id) }}" class="px-3 py-1.5 text-xs rounded-full border border-up-border bg-up-card text-gray-200 hover:border-up-yellow hover:text-up-yellow transition">
                        {{ $category->name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($searchProducts->isNotEmpty())
            <div>
                <h3 class="text-white text-sm font-semibold mb-2">Produk</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($searchProducts as $product)
                    <a href="{{ route('front.category', $product->category->slug ?? $product->category->id) }}?product={{ $product->id }}" class="block border border-up-border bg-up-card rounded-lg p-3 hover:border-up-yellow transition">
                        <div class="text-white text-sm font-semibold truncate">{{ $product->name }}</div>
                        <div class="text-up-textmuted text-[11px] mt-1 truncate">{{ $product->category->name ?? '-' }}</div>
                        <div class="text-up-yellow text-xs font-bold mt-2">Rp {{ number_format($product->price_sell, 0, ',', '.') }}</div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        @endif
    </section>
    @endif

    <section x-data="{ activeSlide: 0, slides: {{ $banners->count() > 0 ? $banners->count() : 1 }} }" x-init="if(slides > 1) setInterval(() => { activeSlide = activeSlide === slides - 1 ? 0 : activeSlide + 1 }, 5000)" class="w-full rounded-2xl overflow-hidden relative shadow-lg bg-gray-900 aspect-[21/9] md:aspect-[24/7]">
        @if($banners->isNotEmpty())
            @foreach($banners as $index => $banner)
            <div x-show="activeSlide === {{ $index }}" x-transition.opacity.duration.500ms class="absolute inset-0">
                @if($banner->media_type === 'image' && $banner->image)
                <img src="{{ Storage::url($banner->image) }}" alt="{{ $banner->title }}" loading="eager" fetchpriority="high" decoding="async" class="w-full h-full object-cover">
                @elseif($banner->media_type === 'video' && $banner->media_content)
                <video class="w-full h-full object-cover" autoplay muted loop playsinline>
                    <source src="{{ Storage::url($banner->media_content) }}" type="video/mp4">
                </video>
                @elseif(in_array($banner->media_type, ['embed', 'html']) && $banner->media_content)
                <iframe class="w-full h-full border-0 bg-transparent" srcdoc="{{ $banner->media_content }}" sandbox="allow-scripts allow-same-origin allow-popups"></iframe>
                @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-r from-gray-900 to-up-nav text-gray-300 text-sm">Media banner tidak tersedia</div>
                @endif
            </div>
            @endforeach
        @else
            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-r from-gray-900 to-up-nav">
                <div class="text-center text-white">
                    <h1 class="text-3xl font-bold mb-2 text-up-yellow">SPARXIE HADIR!</h1>
                    <p class="text-xl">Top Up Sekarang Diskon 10%*</p>
                </div>
            </div>
        @endif
    </section>

    <!-- Tokopedia-style PPOB Section - DYNAMIC -->
    <section class="bg-[#161a29] p-5 md:p-6 rounded-xl border border-up-border shadow-lg mb-8"
             x-data="ppobWidget()" x-init="init()">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
            <!-- Left: Bayar & Tagihan Promo Banner -->
            <div class="lg:col-span-5">
                <h2 class="text-white text-lg md:text-xl font-bold mb-4">Bayar & Tagihan</h2>
                <div class="w-full rounded-lg overflow-hidden relative aspect-[21/9] md:aspect-auto md:h-[160px] shadow-md">
                    @if($ppobPromoBanner)
                        @if($ppobPromoBanner->media_type === 'image' && $ppobPromoBanner->image)
                        <img src="{{ Storage::url($ppobPromoBanner->image) }}" alt="{{ $ppobPromoBanner->title ?? 'Promo' }}" class="w-full h-full object-cover rounded-lg">
                        @elseif($ppobPromoBanner->media_type === 'video' && $ppobPromoBanner->media_content)
                        <video class="w-full h-full object-cover rounded-lg" autoplay muted loop playsinline>
                            <source src="{{ Storage::url($ppobPromoBanner->media_content) }}" type="video/mp4">
                        </video>
                        @elseif(in_array($ppobPromoBanner->media_type, ['embed', 'html']) && $ppobPromoBanner->media_content)
                        <iframe class="w-full h-full border-0 bg-transparent rounded-lg" srcdoc="{{ $ppobPromoBanner->media_content }}" sandbox="allow-scripts allow-same-origin allow-popups"></iframe>
                        @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6">
                            <div class="flex-1">
                                <h3 class="text-white font-bold text-lg mb-1 leading-tight">Makin <span class="text-yellow-300">Hemat</span> di {{ $global_site_name ?? 'PPOBKu' }}</h3>
                                <p class="text-white text-xs mb-3">Bayar tagihan & top up lebih mudah</p>
                                <a href="#" class="inline-block bg-transparent border-2 border-white text-white text-xs font-semibold px-4 py-1.5 rounded-full hover:bg-white hover:text-green-600 transition">Cek Sekarang</a>
                            </div>
                        </div>
                        @endif
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6">
                        <div class="flex-1">
                            <h3 class="text-white font-bold text-lg mb-1 leading-tight">Makin <span class="text-yellow-300">Hemat</span> di {{ $global_site_name ?? 'PPOBKu' }}</h3>
                            <p class="text-white text-xs mb-3">Bayar tagihan & top up lebih mudah</p>
                            <a href="#" class="inline-block bg-transparent border-2 border-white text-white text-xs font-semibold px-4 py-1.5 rounded-full hover:bg-white hover:text-green-600 transition">Cek Sekarang</a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Right: Top Up & Tagihan (Dynamic Tabs) -->
            <div class="lg:col-span-7 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-white text-lg md:text-xl font-bold">Top Up & Tagihan</h2>
                    @if($ppobCategories->isNotEmpty())
                    <a href="#product-grid" class="text-up-yellow text-sm font-semibold hover:underline">Lihat Semua</a>
                    @endif
                </div>
                <div class="border border-up-border rounded-lg bg-up-card p-4 flex-1 flex flex-col justify-center">
                    <!-- Dynamic Tabs from DB categories -->
                    <div class="flex items-center space-x-6 border-b border-up-border mb-4 overflow-x-auto hide-scroll pb-2 text-sm font-medium">
                        @forelse($ppobCategories as $idx => $ppobCat)
                        <button @click="selectTab({{ $ppobCat->id }}, '{{ $ppobCat->slug ?? $ppobCat->id }}')"
                                :class="activeTab === {{ $ppobCat->id }} ? 'text-up-yellow border-b-2 border-up-yellow' : 'text-gray-400 hover:text-gray-200'"
                                class="pb-2 whitespace-nowrap px-1 transition">
                            {{ $ppobCat->name }}
                        </button>
                        @empty
                        <span class="text-gray-500 text-sm">Belum ada kategori PPOB.</span>
                        @endforelse
                        @if($ppobCategories->count() > 4)
                        <button class="text-gray-400 hover:text-gray-200 pb-2 whitespace-nowrap px-1 transition"><i class="fas fa-ellipsis-v"></i></button>
                        @endif
                    </div>
                    <!-- Form -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <label class="block text-gray-400 text-xs mb-1 font-medium">Nomor Tujuan</label>
                            <input type="text" x-model="targetNumber" placeholder="Masukan Nomor" class="w-full bg-[#111620] border border-up-border rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-up-yellow transition">
                        </div>
                        <div class="flex-1">
                            <label class="block text-gray-400 text-xs mb-1 font-medium">Nominal</label>
                            <select x-model="selectedProduct" class="w-full bg-[#111620] border border-up-border rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-up-yellow appearance-none transition">
                                <option value="">Pilih Nominal</option>
                                <template x-for="product in products" :key="product.id">
                                    <option :value="product.id" x-text="product.name + ' - Rp ' + new Intl.NumberFormat('id-ID').format(product.price)"></option>
                                </template>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <a :href="activeCategorySlug ? '/kategori/' + activeCategorySlug : '#'"
                               class="w-full sm:w-auto bg-[#343b54] text-gray-300 font-semibold rounded-lg px-6 py-2.5 text-sm hover:bg-up-yellow hover:text-black transition text-center block">Beli</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Category Pills from DB -->
        <div class="flex overflow-x-auto hide-scroll space-x-3 pb-2 pt-2">
            @foreach($ppobCategories->take(10) as $cat)
            <a href="{{ route('front.category', $cat->slug ?? $cat->id) }}" class="flex items-center flex-shrink-0 space-x-2 border border-up-border bg-up-card rounded-full px-4 py-2 text-xs font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition">
                <i class="{{ $cat->icon ?? 'fas fa-tag' }} text-up-yellow"></i> <span>{{ $cat->name }}</span>
            </a>
            @endforeach
        </div>
    </section>

    <section id="product-grid" class="bg-[#161a29] p-5 md:p-6 rounded-xl border border-up-border shadow-lg scroll-mt-20">
        <div class="flex items-center mb-5 border-b border-up-border pb-4">
            <i class="fas fa-fire text-up-yellow text-2xl mr-3 bg-up-yellow/10 p-2.5 rounded-lg"></i>
            <div>
                <h2 class="text-white text-xl font-bold">Game Populer</h2>
                <p class="text-gray-400 text-sm mt-0.5">Top up game pilihan paling banyak dicari</p>
            </div>
        </div>

        <div class="flex overflow-x-auto hide-scroll space-x-3 pb-2">
            @forelse($popularGames as $game)
            <a href="{{ route('front.category', $game->slug ?? $game->id) }}" class="min-w-[140px] w-[140px] md:min-w-[160px] md:w-[160px] flex-shrink-0 group block border-2 border-transparent hover:border-up-yellow rounded overflow-hidden transition-colors relative bg-up-card shadow-[0_4px_10px_rgba(0,0,0,0.3)]">
                <div class="aspect-[3/4] w-full relative">
                    <img src="{{ $game->thumbnail ? asset('storage/'.$game->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($game->name).'&size=300&background=242a40&color=fff' }}" class="w-full h-full object-cover" alt="{{ $game->name }}" loading="lazy" decoding="async">
                    <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/90 via-black/50 to-transparent">
                        <h3 class="text-white text-xs font-bold leading-tight">{{ $game->name }}</h3>
                    </div>
                </div>
            </a>
            @empty
            <div class="text-gray-400 text-sm">Belum ada game populer.</div>
            @endforelse
        </div>
    </section>

    @php
        $gameCategories = $allGames;
    @endphp

    <section class="bg-[#161a29] p-5 md:p-6 rounded-xl border border-up-border shadow-lg mb-12">
        <div class="flex items-center mb-6 border-b border-up-border pb-4">
            <i class="fas fa-gamepad text-up-yellow text-2xl mr-3 bg-up-yellow/10 p-2.5 rounded-lg"></i>
            <div>
                <h2 class="text-white text-lg md:text-xl font-bold">Semua Game</h2>
                <p class="text-gray-400 text-sm mt-0.5">Pilih game dan layanan yang tersedia</p>
            </div>
        </div>

        @if($gameCategories->isNotEmpty())
        <div class="flex items-center space-x-2.5 overflow-x-auto hide-scroll pb-2 mb-6">
            @foreach($gameCategories->pluck('type')->unique() as $type)
            <a href="{{ route('front.top-up-game') }}#{{ Str::slug($type) }}" class="flex items-center flex-shrink-0 space-x-1.5 border border-up-border bg-up-card rounded-full px-4 py-1.5 text-xs font-medium text-gray-300 hover:border-up-yellow hover:text-up-yellow transition whitespace-nowrap">
                <span class="text-up-yellow text-base"><i class="fas fa-gamepad"></i></span>
                <span class="capitalize">{{ $type }}</span>
            </a>
            @endforeach
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @foreach($gameCategories as $game)
            <a href="{{ route('front.category', $game->slug ?? $game->id) }}" class="block bg-up-card rounded overflow-hidden group hover:-translate-y-1 transition-transform relative border border-transparent hover:border-up-yellow">
                @if($game->is_new)
                <div class="absolute top-0 right-0 bg-up-yellow text-black text-[9px] font-bold px-1.5 py-0.5 rounded-bl z-10">New</div>
                @endif
                <div class="aspect-square w-full relative bg-gray-800">
                    <img src="{{ $game->thumbnail ? asset('storage/'.$game->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($game->name).'&size=300&background=242a40&color=fff' }}" class="w-full h-full object-cover" alt="{{ $game->name }}" loading="lazy" decoding="async">
                </div>
                <div class="p-3">
                    <h3 class="text-white text-[13px] font-bold truncate">{{ $game->name }}</h3>
                    <p class="text-up-textmuted text-[10px] mt-0.5 font-medium uppercase truncate">{{ $game->publisher ?? 'Developer' }}</p>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="border border-dashed border-up-border rounded-lg text-center py-10 text-up-textmuted text-sm">
            Belum ada kategori game aktif.
        </div>
        @endif
    </section>

    <!-- PPOB & Tagihan Section (Dipindah ke atas) -->
    <section class="bg-[#161a29] p-5 rounded-lg border border-up-border shadow-sm mb-12">
        <div class="flex justify-between items-end mb-5 border-b border-up-border pb-3">
            <div>
                <h2 class="text-white text-lg font-bold">Promo dan Acara</h2>
                <p class="text-up-textmuted text-xs mt-1">Berita dan panduan game terbaru</p>
            </div>
            <a href="{{ route('front.article.index') }}" class="bg-[#343b54] text-gray-300 text-[10px] font-bold px-3 py-1.5 rounded hover:bg-gray-600 transition">Lainnya <i class="fas fa-caret-right ml-1"></i></a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @if($latestArticles->isNotEmpty())
                @foreach($latestArticles as $article)
                <a href="{{ route('front.article.show', $article->slug) }}" class="block bg-up-card rounded overflow-hidden group border border-transparent hover:border-up-yellow transition-colors relative">
                    <div class="aspect-[24/9] w-full bg-gray-800 relative overflow-hidden">
                        <img src="{{ $article->image ? asset('storage/'.$article->image) : 'https://ui-avatars.com/api/?name=Promo&background=1d2235&color=fff' }}" alt="{{ $article->title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-up-textmuted text-[10px] font-bold tracking-widest uppercase">PROMO</span>
                            <span class="text-up-textmuted text-[10px]">{{ $article->created_at->format('d M Y') }}</span>
                        </div>
                        <h3 class="text-white text-sm font-bold line-clamp-2 leading-snug group-hover:text-up-yellow transition">{{ $article->title }}</h3>
                    </div>
                </a>
                @endforeach
            @else
                <div class="col-span-full border border-dashed border-up-border rounded-lg text-center py-10 text-up-textmuted text-sm">
                    Belum ada promo & acara saat ini.
                </div>
            @endif
        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('ppobWidget', () => ({
        activeTab: {{ $ppobCategories->first()->id ?? 0 }},
        activeCategorySlug: '{{ $ppobCategories->first()->slug ?? $ppobCategories->first()->id ?? '' }}',
        targetNumber: '',
        selectedProduct: '',
        products: [],

        init() {
            if (this.activeTab) {
                this.fetchProducts(this.activeTab);
            }
        },

        selectTab(id, slug) {
            this.activeTab = id;
            this.activeCategorySlug = slug;
            this.selectedProduct = '';
            this.fetchProducts(id);
        },

        fetchProducts(categoryId) {
            fetch('/api/ppob/products?category_id=' + categoryId)
                .then(r => r.json())
                .then(data => { this.products = data; })
                .catch(() => { this.products = []; });
        }
    }));
});
</script>
@endpush
@endsection

