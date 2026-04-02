@extends('layouts.front')
@section('title', 'Top Up Game Cepat dan Murah')
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
    "name": "Top Up Game Cepat dan Murah",
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
            <div class="lg:col-span-5 flex flex-col">
                <h2 class="text-white text-lg md:text-xl font-bold mb-4">Bayar & Tagihan</h2>
                <div class="w-full rounded-lg overflow-hidden relative aspect-[21/9] md:aspect-auto flex-1 shadow-md">
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
                    @if($ppobGrouped->isNotEmpty())
                    <a href="{{ route('front.ppob') }}" class="text-up-yellow text-sm font-semibold hover:underline">Lihat Semua</a>
                    @endif
                </div>
                <div class="border border-up-border rounded-xl bg-up-card p-4 flex-1 flex flex-col justify-center">
                    <!-- Dynamic Tabs -->
                    @php
                        // Label mapping for multi-category types
                        $typeLabels = [
                            'pulsa'   => 'Pulsa & Data',
                            'e-money' => 'E-Money',
                            'tagihan' => 'PLN & Tagihan',
                            'tv'      => 'TV Kabel',
                            'gas'     => 'Gas',
                            'bpjs'    => 'BPJS',
                            'pdam'    => 'PDAM',
                        ];
                    @endphp

                    <div class="flex items-center space-x-6 border-b border-up-border mb-4 overflow-x-auto hide-scroll pb-2 text-sm font-medium">
                        @forelse($ppobGrouped as $type => $categories)
                        <button @click="selectType('{{ $type }}')"
                                :class="activeType === '{{ $type }}' ? 'text-up-yellow border-b-2 border-up-yellow' : 'text-gray-400 hover:text-gray-200'"
                                class="pb-2 whitespace-nowrap px-1 transition">
                            @if($categories->count() === 1)
                                {{ $categories->first()->name }}
                            @else
                                {{ $typeLabels[$type] ?? ucwords(str_replace(['-', '_'], ' ', $type)) }}
                            @endif
                        </button>
                        @empty
                        <span class="text-gray-500 text-sm">Belum ada kategori PPOB.</span>
                        @endforelse
                    </div>

                    <!-- Form -->
                    <div class="flex flex-col gap-3">

                        <div class="flex-1 w-full" x-show="availableBrands.length > 0" style="display: none;">
                            <label class="block text-gray-400 text-xs mb-1 font-medium">Pilih Provider / Layanan</label>
                            <select x-model="activeBrand" @change="selectBrand($event.target.value)" class="w-full bg-[#111620] border border-up-border rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-up-yellow appearance-none transition">
                                <template x-for="brand in availableBrands" :key="brand.id">
                                    <option :value="brand.id" x-text="brand.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex flex-col md:flex-row gap-3">
                            <!-- Nomor Input -->
                            <div class="flex-1 min-w-[30%]">
                                <label class="block text-gray-400 text-xs mb-1 font-medium"
                                       x-text="activeType === 'pulsa' || activeType === 'e-money' ? 'Nomor Telepon' : 'Nomor Pelanggan'"></label>
                                <input type="text" x-model="targetNumber" placeholder="Masukan Nomor/ID"
                                       class="w-full bg-[#111620] border border-up-border rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-up-yellow transition">
                            </div>

                            <!-- Nominal Produk (Now handles the entire flow) -->
                            <div class="flex-1 min-w-[50%]">
                                <label class="block text-gray-400 text-xs mb-1 font-medium">Nominal / Produk</label>
                                <button type="button" @click="showProductModal = true;"
                                        class="w-full flex items-center justify-between bg-[#111620] border border-up-border rounded-lg px-3 py-2.5 text-white text-sm focus:outline-none focus:border-up-yellow transition">
                                    <span x-text="selectedProduct ? getProductName(selectedProduct) : '— Pilih Produk —'" 
                                          class="block flex-1 truncate text-left mr-2 font-normal"></span>
                                    <i class="fas fa-chevron-down text-[10px] text-gray-400 flex-shrink-0 mt-0.5"></i>
                                </button>
                                <p class="text-[10px] text-gray-600 mt-1" x-show="products.length === 0 && activeBrand">Tidak ada produk tersedia.</p>
                            </div>

                            <!-- Tombol Beli -->
                            <div class="flex items-end mt-1 md:mt-0 pt-[22px]">
                                <a :href="checkoutLink"
                                   class="w-full md:w-auto h-[42px] bg-up-yellow text-black font-bold rounded-lg px-6 flex items-center justify-center hover:bg-yellow-400 transition shadow-md shadow-up-yellow/20"
                                   :class="{'opacity-40 pointer-events-none': !selectedProduct || !targetNumber}">
                                    Beli
                                </a>
                            </div>
                        </div>

                        <!-- Modal/Bottom Sheet Nominal Produk -->
                        <template x-teleport="body">
                            <div x-show="showProductModal" 
                                 class="fixed inset-0 flex items-end md:items-center justify-center bg-black/80 backdrop-blur-sm"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 style="z-index: 99999; display: none;">
                                 
                                <div @click.away="showProductModal = false"
                                     class="bg-[#161a29] w-full md:max-w-lg rounded-t-3xl md:rounded-2xl border border-up-border shadow-2xl flex flex-col"
                                     style="max-height: 85vh;"
                                     x-show="showProductModal"
                                     x-transition:enter="transition ease-out duration-300 transform"
                                     x-transition:enter-start="translate-y-full md:scale-95 md:translate-y-0"
                                     x-transition:enter-end="translate-y-0 md:scale-100 md:translate-y-0"
                                     x-transition:leave="transition ease-in duration-200 transform"
                                     x-transition:leave-start="translate-y-0 md:scale-100 md:translate-y-0"
                                     x-transition:leave-end="translate-y-full md:scale-95 md:translate-y-0">
                                    
                                    <!-- Header Modal -->
                                    <div class="px-5 py-4 border-b border-up-border flex justify-between items-center md:rounded-t-2xl relative">
                                        <div class="flex items-center gap-3">
                                            <button x-show="canGoBack" 
                                                    @click="goBack()" 
                                                    class="text-gray-400 hover:text-white transition w-8 h-8 flex items-center justify-center rounded-full bg-[#111620] hover:bg-gray-700">
                                                <i class="fas fa-arrow-left"></i>
                                            </button>
                                            <h3 class="text-white font-bold text-lg" 
                                                x-text="modalTitle"></h3>
                                        </div>
                                        <button @click="showProductModal = false" class="text-gray-400 hover:text-white transition w-8 h-8 flex items-center justify-center rounded-full bg-[#111620] hover:bg-gray-700">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <!-- Body Modal -->
                                    <div class="p-0 overflow-y-auto hide-scroll flex-1">
                                        
                                        <!-- Step 1: List of Types (Level 1) -->
                                        <div x-show="modalStep === 'types'" class="flex flex-col p-4 gap-3">
                                            <template x-for="type in productTypes.items" :key="type.name">
                                                <button @click="selectedType = type.name; selectedGroup = ''; selectedProduct = '';" 
                                                        class="w-full bg-up-card hover:bg-up-border/50 border border-up-border rounded-xl p-4 flex items-center gap-4 transition-all group cursor-pointer shadow-sm hover:shadow-up-yellow/5">
                                                    <div class="w-12 h-12 rounded-full bg-up-yellow/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                                                        <i :class="getTypeIcon(type.name)" class="text-xl"></i>
                                                    </div>
                                                    <div class="text-left flex-1">
                                                        <h4 class="text-white font-bold text-sm mb-0.5" x-text="type.name"></h4>
                                                        <p class="text-gray-500 text-[10px]" x-text="type.count + ' Produk Tersedia'"></p>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-gray-500 text-xs group-hover:text-up-yellow transition-colors"></i>
                                                </button>
                                            </template>
                                        </div>

                                        <!-- Step 2: List of Groups within Type (Level 2) -->
                                        <div x-show="modalStep === 'groups'" class="flex flex-col p-4 gap-3">
                                            <template x-for="group in productGroups.items" :key="group.name">
                                                <button @click="selectedGroup = group.name; selectedProduct = '';" 
                                                        class="w-full bg-up-card hover:bg-up-border/50 border border-up-border rounded-xl p-4 flex items-center gap-4 transition-all group cursor-pointer shadow-sm hover:shadow-up-yellow/5">
                                                    <div class="w-12 h-12 rounded-full bg-up-yellow/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                                                        <i :class="getGroupIcon(group.name)" class="text-xl"></i>
                                                    </div>
                                                    <div class="text-left flex-1">
                                                        <h4 class="text-white font-bold text-sm mb-0.5" x-text="group.name"></h4>
                                                        <p class="text-gray-500 text-[10px]" x-text="group.count + ' Produk Tersedia'"></p>
                                                    </div>
                                                    <i class="fas fa-chevron-right text-gray-500 text-xs group-hover:text-up-yellow transition-colors"></i>
                                                </button>
                                            </template>
                                        </div>

                                        <!-- Step 3: List Produk Final (Level 3) -->
                                        <div x-show="modalStep === 'products'" class="flex flex-col pb-6">
                                            <template x-for="product in currentList" :key="product.id">
                                                <button type="button" @click="selectedProduct = product.id; showProductModal = false"
                                                        class="w-full text-left px-5 py-4 transition-all duration-200 flex flex-row items-center justify-between cursor-pointer border-b border-up-border/30 last:border-0 group"
                                                        :class="selectedProduct === product.id ? 'bg-up-yellow/10' : 'hover:bg-white/5'">
                                                    
                                                    <div class="text-gray-200 font-medium text-sm leading-tight group-hover:text-white transition-colors flex-1 pr-3" x-text="product.name"></div>
                                                    
                                                    <div class="flex items-center gap-3">
                                                        <div class="text-up-yellow font-bold text-sm whitespace-nowrap" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price_sell ?? product.price)"></div>
                                                        <template x-if="selectedProduct === product.id">
                                                            <i class="fas fa-check-circle text-up-yellow text-sm"></i>
                                                        </template>
                                                    </div>
                                                </button>
                                            </template>
                                            
                                            <!-- Empty State -->
                                            <template x-if="currentList.length === 0">
                                                <div class="text-center py-16 px-5 text-gray-400 text-sm">
                                                    <i class="fas fa-box-open text-5xl mb-4 text-gray-600 block"></i>
                                                    <p class="text-white font-semibold text-base mb-1">Produk Belum Tersedia</p>
                                                    <p class="text-xs">Maaf, saat ini belum ada produk untuk pilihan ini.</p>
                                                </div>
                                            </template>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Shortcut Kapsul Kategori PPOB (Full Width di Bawah) -->
        <div class="pt-5 mt-2 border-t border-up-border/50">
            <!-- Kapsul Pills -->
            <div class="flex items-center space-x-2 overflow-x-auto hide-scroll pb-3">
                @foreach($ppobGrouped as $type => $cats)
                @php
                    $capsuleIcons = [
                        'pulsa'   => 'fa-mobile-alt',
                        'e-money' => 'fa-wallet',
                        'tagihan' => 'fa-bolt',
                        'ppob'    => 'fa-receipt',
                        'tv'      => 'fa-tv',
                        'gas'     => 'fa-fire',
                        'bpjs'    => 'fa-heartbeat',
                        'pdam'    => 'fa-tint',
                    ];
                    $capsuleLabels = [
                        'pulsa'   => 'Pulsa & Data',
                        'e-money' => 'E-Money',
                        'tagihan' => 'PLN & Tagihan',
                        'ppob'    => 'Layanan PPOB',
                        'tv'      => 'TV Kabel',
                        'gas'     => 'PGN / Gas',
                        'bpjs'    => 'BPJS',
                        'pdam'    => 'PDAM',
                    ];
                    $icon = $capsuleIcons[$type] ?? 'fa-tag';
                    $label = $capsuleLabels[$type] ?? ucwords(str_replace(['-','_'],' ', $type));
                @endphp
                <button type="button"
                        @click="expandedType = (expandedType === '{{ $type }}') ? '' : '{{ $type }}'"
                        :class="expandedType === '{{ $type }}' ? 'border-up-yellow text-up-yellow bg-up-yellow/10' : 'border-up-border text-gray-300 bg-up-card hover:border-up-yellow hover:text-up-yellow'"
                        class="flex items-center flex-shrink-0 space-x-2 border rounded-full px-4 py-2 text-xs font-bold transition whitespace-nowrap shadow-sm group">
                    <span :class="expandedType === '{{ $type }}' ? 'text-up-yellow' : 'text-up-yellow group-hover:text-up-yellow'"><i class="fas {{ $icon }}"></i></span>
                    <span>{{ $label }}</span>
                    <i class="fas fa-chevron-down text-[9px] ml-1 transition-transform" :class="expandedType === '{{ $type }}' ? 'rotate-180' : ''"></i>
                </button>
                @endforeach
            </div>

            <!-- Grid Kategori Expandable -->
            <div x-show="expandedType !== ''" x-transition.opacity class="mt-3" style="display:none;">
                @foreach($ppobGrouped as $type => $cats)
                <div x-show="expandedType === '{{ $type }}'" x-transition.opacity class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3" style="display:none;">
                    @foreach($cats as $cat)
                    <a href="{{ route('front.category', $cat->slug ?? $cat->id) }}"
                       class="flex flex-col items-center text-center p-3 rounded-xl bg-up-card border border-up-border hover:border-up-yellow hover:bg-up-yellow/5 transition group">
                        <div class="w-12 h-12 rounded-xl overflow-hidden mb-2 bg-gray-800 border border-up-border group-hover:border-up-yellow/50 transition flex-shrink-0">
                            <img src="{{ $cat->thumbnail ? asset('storage/' . $cat->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($cat->name).'&size=96&background=242a40&color=facc15&bold=true&font-size=0.4' }}"
                                 alt="{{ $cat->name }}"
                                 class="w-full h-full object-cover"
                                 loading="lazy">
                        </div>
                        <span class="text-white text-[10px] font-semibold leading-tight line-clamp-2 group-hover:text-up-yellow transition">{{ $cat->name }}</span>
                    </a>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== ALL CATEGORIES SECTION (MARQUEE) ===================== --}}
    @if($allCategories->isNotEmpty())
    <section class="bg-[#161a29] p-5 md:p-6 rounded-xl border border-up-border shadow-lg mb-6 overflow-hidden">
        <div class="flex items-center justify-between mb-4 border-b border-up-border pb-4">
            <div class="flex items-center gap-3">
                <div class="bg-up-yellow/10 p-2.5 rounded-lg">
                    <i class="fas fa-th-large text-up-yellow text-xl"></i>
                </div>
                <div>
                    <h2 class="text-white text-lg md:text-xl font-bold">Semua Kategori</h2>
                    <p class="text-gray-400 text-sm mt-0.5">Pilih kategori layanan yang kamu butuhkan</p>
                </div>
            </div>
            <a href="{{ route('front.top-up-game') }}" class="text-up-yellow text-xs font-semibold hover:underline whitespace-nowrap">Lihat Semua <i class="fas fa-caret-right ml-0.5"></i></a>
        </div>

        {{-- Marquee Container --}}
        <div class="relative overflow-hidden">
            <div class="marquee-track flex gap-3" id="cat-marquee">
                @php
                    // Duplikat 2x untuk seamless loop
                    $marqueeItems = $allCategories->concat($allCategories);
                @endphp
                @foreach($marqueeItems as $cat)
                <a href="{{ route('front.category', $cat->slug ?? $cat->id) }}"
                   class="marquee-card flex-shrink-0 flex flex-col items-center text-center p-3 rounded-xl bg-up-card border border-up-border hover:border-up-yellow transition-colors group"
                   style="width: 120px;">
                    <div class="w-12 h-12 rounded-xl overflow-hidden mb-2 bg-gray-800 border border-up-border group-hover:border-up-yellow/50 transition flex-shrink-0">
                        <img src="{{ $cat->thumbnail ? asset('storage/' . $cat->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($cat->name).'&size=88&background=242a40&color=facc15&bold=true&font-size=0.4' }}"
                             alt="{{ $cat->name }}"
                             class="w-full h-full object-cover"
                             loading="lazy" decoding="async">
                    </div>
                    <span class="text-white text-[10px] font-semibold leading-tight line-clamp-2 group-hover:text-up-yellow transition w-full">{{ $cat->name }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    {{-- ================================================================== --}}



    <section id="product-grid" class="bg-[#161a29] p-5 md:p-6 rounded-xl border border-up-border shadow-lg scroll-mt-20">
        <div class="flex items-center mb-5 border-b border-up-border pb-4">
            <i class="fas fa-fire text-orange-400 text-2xl mr-3 bg-orange-500/10 p-2.5 rounded-lg fire-icon-anim"></i>
            <div>
                <h2 class="text-white text-xl font-bold">Game Populer</h2>
                <p class="text-gray-400 text-sm mt-0.5">Top up game pilihan paling banyak dicari</p>
            </div>
        </div>

        <style>
            /* ======= FIRE CARD ANIMATION V2 ======= */
            @keyframes fire-glow-subtle {
                0%,100% { box-shadow: 0 0 5px 1px #f9731688, 0 4px 10px #dc262644; }
                50%      { box-shadow: 0 0 10px 2px #fbbf24aa, 0 4px 15px #ea580c55; }
            }
            @keyframes fire-border-subtle {
                0%,100% { border-color: #f97316; }
                50%      { border-color: #fbbf24; }
            }
            @keyframes flame-rise {
                0%   { transform: scaleX(1)   scaleY(1)    translateY(0); opacity: 0.8; }
                50%  { transform: scaleX(0.9) scaleY(1.2)  translateY(-2px); opacity: 1; }
                100% { transform: scaleX(1)   scaleY(1)    translateY(0); opacity: 0.8; }
            }
            @keyframes fire-icon-pulse {
                0%,100% { color: #f97316; text-shadow: 0 0 5px #f9731666; }
                50%      { color: #fbbf24; text-shadow: 0 0 10px #fbbf2488; }
            }
            .fire-card {
                animation: fire-glow-subtle 3s ease-in-out infinite, fire-border-subtle 3s ease-in-out infinite;
                border: 1.5px solid #f97316;
                position: relative;
                overflow: hidden; /* Mencegah elemen keluar batas */
                transition: transform 0.3s ease;
            }
            .fire-card:hover { transform: translateY(-4px); }
            .fire-card:nth-child(2) { animation-delay: 0.5s; }
            .fire-card:nth-child(3) { animation-delay: 1.0s; }
            .fire-card:nth-child(4) { animation-delay: 1.5s; }
            .fire-card:nth-child(5) { animation-delay: 2.0s; }
            .fire-card:nth-child(6) { animation-delay: 0.2s; }

            .fire-card .flame-wrap {
                position: absolute;
                bottom: -2px; /* Sesuaikan dgn overflow hidden */
                left: 0; right: 0;
                height: 20px;
                pointer-events: none;
                z-index: 10;
                display: flex;
                justify-content: space-evenly;
                align-items: flex-end;
            }
            .flame {
                width: 12px;
                border-radius: 50% 50% 20% 20%;
                animation: flame-rise 1.5s ease-in-out infinite;
                filter: blur(1px);
                opacity: 0.9;
            }
            .flame-1 { background: linear-gradient(to top, #f97316, transparent); animation-delay: 0s;    height: 14px; }
            .flame-2 { background: linear-gradient(to top, #ef4444, transparent); animation-delay: 0.3s;  height: 18px; width: 10px; }
            .flame-3 { background: linear-gradient(to top, #fbbf24, transparent); animation-delay: 0.6s;  height: 12px; }
            .flame-4 { background: linear-gradient(to top, #ea580c, transparent); animation-delay: 0.2s;  height: 16px; }
            .flame-5 { background: linear-gradient(to top, #fcd34d, transparent); animation-delay: 0.5s;  height: 10px; }

            .fire-icon-anim { animation: fire-icon-pulse 2s ease-in-out infinite; }
        </style>

        <div class="flex overflow-x-auto hide-scroll space-x-4 pb-4 pt-2 px-2 -mx-2">
            @forelse($popularGames as $game)
            <a href="{{ route('front.category', $game->slug ?? $game->id) }}"
               class="fire-card min-w-[140px] w-[140px] md:min-w-[160px] md:w-[160px] flex-shrink-0 group block rounded-lg bg-up-card shadow-lg">
                <div class="aspect-[3/4] w-full relative">
                    <img src="{{ $game->thumbnail ? asset('storage/'.$game->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($game->name).'&size=300&background=1a1f35&color=fff' }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                         alt="{{ $game->name }}" loading="lazy" decoding="async">
                    {{-- Gradient overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                    {{-- Name --}}
                    <div class="absolute bottom-0 left-0 right-0 p-3 pb-4 z-20">
                        <h3 class="text-white text-xs font-bold leading-tight drop-shadow-md">{{ $game->name }}</h3>
                    </div>
                    {{-- Flame particles --}}
                    <div class="flame-wrap">
                        <div class="flame flame-1"></div>
                        <div class="flame flame-2"></div>
                        <div class="flame flame-3"></div>
                        <div class="flame flame-4"></div>
                        <div class="flame flame-5"></div>
                    </div>
                </div>
            </a>
            @empty
            <div class="text-gray-400 text-sm pl-2">Belum ada game populer.</div>
            @endforelse
        </div>
    </section>

    {{-- ═══════ PRODUK TERLARIS — MARQUEE + GLOW ═══════ --}}
    @if($topSellingCategories->count() > 0)
    <section class="mb-8">
        <div class="flex items-center justify-between mb-4 px-1">
            <div class="flex items-center gap-2.5">
                <i class="fas fa-fire-alt text-orange-400 text-lg"></i>
                <h2 class="text-white text-base md:text-lg font-bold">Terlaris</h2>
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-400"></span>
                </span>
            </div>
        </div>

        <div class="overflow-hidden -mx-3 px-3 py-2">
        <div class="flex gap-3 md:gap-4" id="terlaris-marquee">
            @php $terlarisItems = $topSellingCategories->concat($topSellingCategories); @endphp
            @foreach($terlarisItems as $topCat)
            <a href="{{ route('front.category', $topCat->slug ?? $topCat->id) }}" class="flex-shrink-0 flex items-center gap-3 bg-[#1e2235] rounded-xl px-4 py-3 md:px-5 md:py-3.5 border border-transparent hover:border-orange-400/40 transition-all group {{ $loop->index % $topSellingCategories->count() < 3 ? 'border-orange-400/20' : '' }}">
                {{-- Ranking badge --}}
                <div class="relative flex-shrink-0">
                    <img src="{{ $topCat->thumbnail ? asset('storage/'.$topCat->thumbnail) : 'https://ui-avatars.com/api/?name='.urlencode($topCat->name).'&size=120&background=242a40&color=fff' }}"
                         class="w-12 h-12 md:w-14 md:h-14 rounded-lg object-cover" alt="{{ $topCat->name }}" loading="lazy">
                    @if($loop->index % $topSellingCategories->count() < 3)
                    @php $rank = $loop->index % $topSellingCategories->count(); @endphp
                    <div class="absolute -top-2 -left-2 w-5 h-5 md:w-6 md:h-6 rounded-full flex items-center justify-center text-[10px] md:text-xs font-black terlaris-badge
                        {{ $rank === 0 ? 'bg-yellow-400 text-black terlaris-glow-gold' : ($rank === 1 ? 'bg-gray-300 text-black terlaris-glow-silver' : 'bg-orange-500 text-white terlaris-glow-bronze') }}">
                        {{ $rank + 1 }}
                    </div>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-white text-xs md:text-sm font-semibold truncate max-w-[120px] md:max-w-[150px]">{{ $topCat->name }}</p>
                    <p class="text-orange-400 text-[11px] md:text-xs font-medium mt-0.5">{{ number_format($topCat->transaction_count) }} trx</p>
                </div>
            </a>
            @endforeach
        </div>
        </div>
    </section>
    <style>
        @keyframes terlaris-glow-pulse-gold {
            0%, 100% { box-shadow: 0 0 4px 1px rgba(250, 204, 21, 0.4); }
            50%      { box-shadow: 0 0 8px 3px rgba(250, 204, 21, 0.7); }
        }
        @keyframes terlaris-glow-pulse-silver {
            0%, 100% { box-shadow: 0 0 4px 1px rgba(209, 213, 219, 0.35); }
            50%      { box-shadow: 0 0 8px 3px rgba(209, 213, 219, 0.6); }
        }
        @keyframes terlaris-glow-pulse-bronze {
            0%, 100% { box-shadow: 0 0 4px 1px rgba(249, 115, 22, 0.35); }
            50%      { box-shadow: 0 0 8px 3px rgba(249, 115, 22, 0.65); }
        }
        .terlaris-glow-gold   { animation: terlaris-glow-pulse-gold 2s ease-in-out infinite; }
        .terlaris-glow-silver { animation: terlaris-glow-pulse-silver 2.4s ease-in-out infinite; }
        .terlaris-glow-bronze { animation: terlaris-glow-pulse-bronze 2.8s ease-in-out infinite; }
    </style>
    @endif


    @php $gameCategories = $allGames; @endphp


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

        <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
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
        groupedCategories: @json($ppobGrouped),
        activeType: '{{ $ppobGrouped->keys()->first() ?? "" }}',
        activeBrand: '',
        activeBrandSlug: '',
        availableBrands: [],
        targetNumber: '',
        selectedType: '',
        selectedGroup: '',
        selectedProduct: '',
        products: [],
        showProductModal: false,

        expandedType: '',

        init() {
            if (this.activeType) {
                this.selectType(this.activeType);
            }
        },

        selectType(type) {
            this.activeType = type;
            this.availableBrands = this.groupedCategories[type] || [];
            this.selectedProduct = '';
            this.selectedType = '';
            this.selectedGroup = '';
            this.showProductModal = false;
            if (this.availableBrands.length > 0) {
                this.selectBrand(this.availableBrands[0].id);
            } else {
                this.products = [];
                this.activeBrand = '';
                this.activeBrandSlug = '';
            }
        },

        selectBrand(brandId) {
            this.activeBrand = brandId;
            let brandObj = this.availableBrands.find(b => parseInt(b.id) === parseInt(brandId));
            this.activeBrandSlug = brandObj ? (brandObj.slug || brandObj.id) : '';
            this.selectedProduct = '';
            this.selectedType = '';
            this.selectedGroup = '';
            this.showProductModal = false;
            this.fetchProducts(brandId);
        },

        fetchProducts(categoryId) {
            fetch('/api/ppob/products?category_id=' + categoryId)
                .then(r => r.json())
                .then(data => {
                    this.products = Array.isArray(data) ? data : [];
                    this.selectedType = '';
                    this.selectedGroup = '';

                    // Auto-select if no hierarchy needed
                    let types = this.productTypes;
                    if (!types.hasTypes) {
                        let groups = this.productGroups;
                        if (!groups.hasGroups && groups.items.length === 1) {
                            this.selectedGroup = groups.items[0].name;
                        }
                    }
                })
                .catch(() => { this.products = []; this.selectedType = ''; this.selectedGroup = ''; });
        },

        // Level 1: product_type grouping
        get productTypes() {
            if (!this.products || this.products.length === 0) return { items: [], hasTypes: false };
            let map = {};
            let typeCount = 0;
            this.products.forEach(p => {
                if (p.product_type) {
                    if (!map[p.product_type]) {
                        map[p.product_type] = { name: p.product_type, count: 0 };
                        typeCount++;
                    }
                    map[p.product_type].count++;
                }
            });
            let items = Object.values(map).sort((a,b) => a.name.localeCompare(b.name));
            return { items, hasTypes: typeCount > 1 };
        },

        // Level 2: product_group grouping within selected type
        get productGroups() {
            if (!this.products || this.products.length === 0) return { items: [], hasGroups: false };
            let filtered = this.products;
            if (this.productTypes.hasTypes && this.selectedType) {
                filtered = this.products.filter(p => p.product_type === this.selectedType);
            }
            let map = {};
            let groupCount = 0;
            filtered.forEach(p => {
                if (p.product_group) {
                    if (!map[p.product_group]) {
                        map[p.product_group] = { name: p.product_group, count: 0 };
                        groupCount++;
                    }
                    map[p.product_group].count++;
                }
            });
            let items = Object.values(map).sort((a,b) => a.name.localeCompare(b.name));
            return { items, hasGroups: groupCount > 1 };
        },

        // Level 3: filtered product list
        get currentList() {
            let list = this.products;

            // Filter by type if applicable
            if (this.productTypes.hasTypes) {
                if (!this.selectedType) return [];
                list = list.filter(p => p.product_type === this.selectedType);
            }

            // Filter by group if applicable
            if (this.productGroups.hasGroups) {
                if (!this.selectedGroup) return [];
                list = list.filter(p => p.product_group === this.selectedGroup);
            }

            return list;
        },

        // Determine current modal step
        get modalStep() {
            if (this.productTypes.hasTypes && !this.selectedType) return 'types';
            if (this.productGroups.hasGroups && !this.selectedGroup) return 'groups';
            return 'products';
        },

        get modalTitle() {
            if (this.modalStep === 'types') return 'Pilih Tipe Layanan';
            if (this.modalStep === 'groups') return 'Pilih Paket';
            return 'Pilih Nominal';
        },

        get canGoBack() {
            if (this.modalStep === 'groups' && this.productTypes.hasTypes) return true;
            if (this.modalStep === 'products') {
                if (this.productGroups.hasGroups) return true;
                if (this.productTypes.hasTypes) return true;
            }
            return false;
        },

        goBack() {
            if (this.modalStep === 'products' && this.productGroups.hasGroups && this.selectedGroup) {
                this.selectedGroup = '';
                this.selectedProduct = '';
            } else if (this.modalStep === 'products' && this.productTypes.hasTypes) {
                this.selectedType = '';
                this.selectedGroup = '';
                this.selectedProduct = '';
            } else if (this.modalStep === 'groups') {
                this.selectedType = '';
                this.selectedGroup = '';
                this.selectedProduct = '';
            }
        },

        getTypeIcon(typeName) {
            let lower = String(typeName).toLowerCase();
            if (lower.includes('data') || lower.includes('internet')) return 'fas fa-globe text-up-yellow';
            if (lower.includes('pulsa') || lower.includes('reguler')) return 'fas fa-mobile-alt text-up-yellow';
            if (lower.includes('telepon') || lower.includes('sms')) return 'fas fa-phone text-up-yellow';
            if (lower.includes('driver')) return 'fas fa-motorcycle text-up-yellow';
            if (lower.includes('customer') || lower.includes('penumpang')) return 'fas fa-user text-up-yellow';
            if (lower.includes('token') || lower.includes('listrik')) return 'fas fa-bolt text-up-yellow';
            if (lower.includes('global') || lower.includes('indonesia') || lower.includes('malaysia')) return 'fas fa-globe-asia text-up-yellow';
            if (lower.includes('starlight')) return 'fas fa-star text-up-yellow';
            return 'fas fa-box text-up-yellow';
        },

        getGroupIcon(groupName) {
            let lower = String(groupName).toLowerCase();
            if (lower.includes('data') || lower.includes('internet')) return 'fas fa-globe text-up-yellow';
            if (lower.includes('pulsa') || lower.includes('reguler')) return 'fas fa-mobile-alt text-up-yellow';
            if (lower.includes('driver')) return 'fas fa-motorcycle text-up-yellow';
            if (lower.includes('nelfon') || lower.includes('sms')) return 'fas fa-phone text-up-yellow';
            return 'fas fa-box text-up-yellow';
        },

        // Utility untuk mengambil nama produk terpilih
        getProductName(id) {
            let p = this.products.find(x => parseInt(x.id) === parseInt(id));
            if (p) {
                let formattedPrice = new Intl.NumberFormat('id-ID').format(p.price_sell ?? p.price);
                return p.name + ' · Rp' + formattedPrice;
            }
            return '';
        },

        get checkoutLink() {
            if (!this.activeBrandSlug) return '#';
            let link = '/kategori/' + this.activeBrandSlug;
            let params = new URLSearchParams();
            if (this.selectedProduct) params.append('product', this.selectedProduct);
            if (this.targetNumber) params.append('target', this.targetNumber);
            if (params.toString()) link += '?' + params.toString();
            return link;
        }
    }));
});
    // ---- Category Marquee Scroll ----
    (function() {
        const track = document.getElementById('cat-marquee');
        if (!track) return;

        // Total cards = 2x original (duplicated in PHP for seamless loop)
        const totalCards = track.children.length;
        const halfCards  = totalCards / 2;

        // Get the total width of the first half (original set)
        // card width = 120px + gap 12px = 132px each
        const cardW = 132;
        const totalW = halfCards * cardW;

        // Inject keyframe dynamically
        const style = document.createElement('style');
        style.textContent = `
            #cat-marquee {
                animation: marquee-scroll 120s linear infinite;
            }
            #cat-marquee:hover {
                animation-play-state: paused;
            }
            @keyframes marquee-scroll {
                0%   { transform: translateX(0); }
                100% { transform: translateX(-${totalW}px); }
            }
        `;
        document.head.appendChild(style);

        // Pause on touch
        track.addEventListener('touchstart', () => track.style.animationPlayState = 'paused');
        track.addEventListener('touchend',   () => track.style.animationPlayState = 'running');
    })();

    // ---- Produk Terlaris Marquee Scroll ----
    (function() {
        const track = document.getElementById('terlaris-marquee');
        if (!track) return;

        const totalCards = track.children.length;
        const halfCards  = totalCards / 2;

        // Measure actual first-half width (card width varies with text)
        let totalW = 0;
        const gap = 10; // gap-2.5 = 10px
        for (let i = 0; i < halfCards; i++) {
            totalW += track.children[i].offsetWidth + gap;
        }

        const duration = Math.max(20, halfCards * 4); // ~4s per card

        const style = document.createElement('style');
        style.textContent = `
            #terlaris-marquee {
                animation: terlaris-scroll ${duration}s linear infinite;
            }
            #terlaris-marquee:hover {
                animation-play-state: paused;
            }
            @keyframes terlaris-scroll {
                0%   { transform: translateX(0); }
                100% { transform: translateX(-${totalW}px); }
            }
        `;
        document.head.appendChild(style);

        track.addEventListener('touchstart', () => track.style.animationPlayState = 'paused');
        track.addEventListener('touchend',   () => track.style.animationPlayState = 'running');
    })();
</script>
@endpush
@endsection

