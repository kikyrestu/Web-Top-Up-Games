@extends('layouts.front')
@php
    $isFav = auth()->check() && \App\Models\FavoriteGame::where('user_id', auth()->id())->where('category_id', $category->id)->exists();
@endphp
@section('title', $category->name . ' Top Up Murah')
@section('meta_description', 'Top up ' . $category->name . ' cepat dan aman. Pilih nominal, metode pembayaran lengkap, dan proses otomatis.')
@section('canonical', route('front.category', $category->slug ?? $category->id))
@if(!empty($category->thumbnail))
    @section('meta_image', asset('storage/' . $category->thumbnail))
@endif
@push('jsonld')
<script type="application/ld+json">
{
    "{{ '@' }}context": "https://schema.org",
    "{{ '@' }}type": "WebPage",
    "name": "{{ $category->name }} Top Up Murah",
    "url": "{{ route('front.category', $category->slug ?? $category->id) }}",
    "description": "Top up {{ $category->name }} cepat dan aman dengan metode pembayaran lengkap."
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
            "name": "{{ $category->name }}",
            "item": "{{ route('front.category', $category->slug ?? $category->id) }}"
        }
    ]
}
</script>
@php
    $categoryProductsSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'Daftar produk ' . $category->name,
        'numberOfItems' => $products->count(),
        'itemListElement' => $products->take(20)->values()->map(function ($product, $index) use ($category) {
            return [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'item' => [
                    '@type' => 'Product',
                    'name' => $product->name,
                    'sku' => (string) (optional($product->providerMappings->first())->provider_product_code ?? $product->id),
                    'category' => $category->name,
                    'url' => route('front.category', $category->slug ?? $category->id),
                    'brand' => [
                        '@type' => 'Brand',
                        'name' => $category->publisher ?: ($global_site_name ?? 'PPOBKu'),
                    ],
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => route('front.category', $category->slug ?? $category->id),
                        'priceCurrency' => 'IDR',
                        'price' => (float) $product->price_sell,
                        'availability' => 'https://schema.org/InStock',
                        'itemCondition' => 'https://schema.org/NewCondition',
                    ],
                ],
            ];
        })->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($categoryProductsSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<!-- Hero Background & Category Info -->
<div class="relative w-full h-[280px] md:h-[400px]">
    <!-- Background Image -->
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $category->thumbnail ? asset('storage/'.$category->thumbnail) : 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&q=80' }}'); filter: brightness(0.35);"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#121212] via-[#121212]/20 to-[#121212]/80"></div>

    <div class="container mx-auto px-4 pt-6 relative">
        <nav class="text-sm text-gray-300 drop-shadow-md mb-4 flex items-center" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-2">
                <li>
                    <a href="{{ route('front.index') }}" class="hover:text-white transition">Beranda</a>
                </li>
                <li>
                    <i class="fas fa-chevron-right text-[10px] text-gray-400"></i>
                </li>
                <li class="text-[#f97316] font-medium">{{ $category->name }}</li>
            </ol>
        </nav>
    </div>
    
    <!-- Game Detail Overlay -->
    <div class="absolute bottom-0 left-0 w-full px-4 md:px-8 lg:px-20 pb-4 flex items-end container mx-auto gap-4 md:gap-6" style="margin-bottom: -3rem;">
        <img src="{{ $category->thumbnail ? asset('storage/'.$category->thumbnail) : 'https://placehold.co/150' }}" alt="{{ $category->name }}" class="w-24 h-24 md:w-40 md:h-40 rounded-xl object-cover border-2 md:border-4 border-[#1c1c1c] shadow-2xl shrink-0">
        <div class="pb-1 md:pb-2 flex-grow">
            <h1 class="text-2xl md:text-5xl font-black text-white uppercase italic tracking-wider mb-0.5 md:mb-1 drop-shadow-lg">{{ $category->name }}</h1>
            <p class="text-gray-400 text-xs md:text-base mb-2 md:mb-4 font-medium">{{ $category->publisher ?? 'Publisher Unknown' }}</p>
            <div class="flex flex-wrap gap-1 md:gap-2 text-[10px] md:text-xs font-semibold text-gray-300">
                <span class="bg-[#1c1c1c]/90 px-2 py-1 md:px-3 md:py-1.5 rounded text-[#f59e0b] border border-[#2d2d2d] flex items-center gap-1.5"><i class="fas fa-bolt"></i> Proses Cepat</span>
                <span class="bg-[#1c1c1c]/90 px-2 py-1 md:px-3 md:py-1.5 rounded text-blue-400 border border-[#2d2d2d] flex items-center gap-1.5"><i class="fas fa-headset"></i> Layanan Chat 24/7</span>
                <span class="bg-[#1c1c1c]/90 px-2 py-1 md:px-3 md:py-1.5 rounded text-green-400 border border-[#2d2d2d] flex items-center gap-1.5 hidden sm:flex"><i class="fas fa-shield-alt"></i> Pembayaran Aman</span>
            </div>
        </div>
        @auth
        <button @click="toggleFavorite" type="button" class="mb-2 md:mb-4 bg-[#1c1c1c]/90 hover:bg-[#2d2d2d] border border-[#2d2d2d] w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition shadow-lg shrink-0" :class="isFavorite ? 'text-rose-500' : 'text-gray-400'" title="Tambah ke Favorit">
            <i class="fas fa-heart text-lg md:text-xl" :class="isFavorite ? 'fa-solid' : 'fa-regular'"></i>
        </button>
        @else
        <a href="{{ route('login') }}" class="mb-2 md:mb-4 bg-[#1c1c1c]/90 hover:bg-rose-900/30 border border-[#2d2d2d] hover:border-rose-500/50 w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center transition shadow-lg shrink-0 text-gray-500" title="Login untuk Simpan Favorit">
            <i class="fas fa-heart text-lg md:text-xl"></i>
        </a>
        @endauth
    </div>
</div>

<!-- Main Content wrapper -->
<div class="container mx-auto px-4 mt-20 md:mt-24 pb-20" x-data="checkoutPage()">
    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Col (Form) -->
        <div class="lg:col-span-8 flex flex-col gap-5 md:gap-6">
           
            <!-- 1. Masukkan Data Akun -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-[#f97316] text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">1</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Masukkan Data Akun</h2>
                </div>
                <div class="p-4 md:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($formFields as $field)
                        <div>
                            <label class="block text-gray-400 text-xs mb-1.5 font-medium">{{ $field['label'] ?? 'Field' }}</label>
                            <input type="text" 
                                   x-model="{{ $field['name'] === 'target_zone' ? 'targetZone' : $field['name'] }}" 
                                   placeholder="{{ $field['placeholder'] ?? '' }}" 
                                   {{ ($field['required'] ?? false) ? 'required' : '' }}
                                   @if($supportsIdValidation ?? false) @keyup.enter="validateGameId()" @endif
                                   class="w-full bg-[#121212] border border-[#333] px-4 py-3 rounded-lg focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition text-white placeholder-gray-500 text-sm">
                        </div>
                        @endforeach
                    </div>

                    {{-- Cek Username / ID Validation --}}
                    @if($supportsIdValidation ?? false)
                    <div class="mt-4">
                        <button type="button" 
                                @click="validateGameId()"
                                :disabled="isValidating || !target"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 border"
                                :class="!target || isValidating 
                                    ? 'bg-[#1a1a1a] text-gray-500 border-[#333] cursor-not-allowed' 
                                    : 'bg-blue-600/20 text-blue-400 border-blue-500/50 hover:bg-blue-600/30 hover:text-blue-300 hover:border-blue-400'">
                            <template x-if="!isValidating">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-search"></i> Cek Username
                                </span>
                            </template>
                            <template x-if="isValidating">
                                <span class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Mengecek...
                                </span>
                            </template>
                        </button>
                        <span class="text-[11px] text-gray-500 ml-2">Tekan Enter atau klik untuk validasi ID</span>

                        {{-- Validation Result --}}
                        <div x-show="validationResult !== null" x-transition class="mt-3" style="display:none;">
                            {{-- Success --}}
                            <div x-show="validationResult && validationResult.success" 
                                 class="flex items-center gap-3 bg-green-900/30 border border-green-500/40 rounded-lg px-4 py-3">
                                <div class="w-9 h-9 rounded-full bg-green-500/20 flex items-center justify-center shrink-0">
                                    <i class="fas fa-check-circle text-green-400 text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-green-300 text-sm font-bold">ID Valid!</p>
                                    <p class="text-green-400 text-xs">
                                        Username: <span class="font-black text-sm text-white" x-text="validationResult?.nickname"></span>
                                    </p>
                                </div>
                            </div>
                            {{-- Error --}}
                            <div x-show="validationResult && !validationResult.success" 
                                 class="flex items-center gap-3 bg-red-900/30 border border-red-500/40 rounded-lg px-4 py-3">
                                <div class="w-9 h-9 rounded-full bg-red-500/20 flex items-center justify-center shrink-0">
                                    <i class="fas fa-times-circle text-red-400 text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-red-300 text-sm font-bold">ID Tidak Ditemukan</p>
                                    <p class="text-red-400/80 text-xs" x-text="validationResult?.message"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- 2. Pilih Nominal -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-[#f97316] text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">2</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Pilih Nominal</h2>
                </div>
                <div class="p-4 md:p-6">
                    
                    @if($products->isEmpty())
                        <div class="text-center text-gray-500 py-8 bg-[#121212] rounded-lg border border-dashed border-[#333]">
                            <i class="fas fa-box-open text-3xl mb-2 text-gray-600"></i>
                            <p class="text-sm text-gray-400">Pilihan nominal belum tersedia.</p>
                        </div>
                    @else
                        <!-- Level 1: Type Tabs (inline pills) -->
                        <div x-show="hasTypes" class="mb-4">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="type in productTypes" :key="type.name">
                                    <button type="button" @click="selectTypeLevel(type.name)"
                                            class="px-4 py-2 rounded-lg text-sm font-bold transition-all duration-200 border flex items-center gap-1.5"
                                            :class="selectedType === type.name 
                                                ? 'bg-[#f97316] text-white border-[#f97316] shadow-[0_0_12px_rgba(249,115,22,0.3)]' 
                                                : 'bg-[#1a1a1a] text-gray-400 border-[#333] hover:border-[#f97316]/50 hover:text-white'">
                                        <img x-show="getFlagUrl(type.name)" :src="getFlagUrl(type.name)" :alt="type.name" class="w-5 h-4 object-cover rounded-sm" loading="lazy">
                                        <i x-show="getFlagCode(type.name) === 'global'" class="fas fa-globe text-sm"></i>
                                        <span x-text="type.name"></span>
                                        <span class="ml-0.5 text-[10px] opacity-70" x-text="'(' + type.count + ')'"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Level 2: Sub-group Tabs (inline pills) -->
                        <div x-show="hasTypes && selectedType && hasSubGroups" class="mb-4">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="group in currentSubGroups" :key="group.name">
                                    <button type="button" @click="selectGroup(group.name)"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 border flex items-center gap-1"
                                            :class="selectedGroup === group.name 
                                                ? 'bg-[#f97316]/20 text-[#f97316] border-[#f97316]/50' 
                                                : 'bg-[#1a1a1a] text-gray-500 border-[#2d2d2d] hover:border-[#f97316]/30 hover:text-gray-300'">
                                        <img x-show="getFlagUrl(group.name)" :src="getFlagUrl(group.name)" :alt="group.name" class="w-4 h-3 object-cover rounded-sm" loading="lazy">
                                        <i x-show="getFlagCode(group.name) === 'global'" class="fas fa-globe text-xs"></i>
                                        <span x-text="group.name"></span>
                                        <span class="ml-0.5 text-[10px] opacity-60" x-text="'(' + group.count + ')'"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Level 2: Group Tabs when no types exist -->
                        <div x-show="!hasTypes && hasGroups" class="mb-4">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="group in productGroups" :key="group.name">
                                    <button type="button" @click="selectGroup(group.name)"
                                            class="px-4 py-2 rounded-lg text-sm font-bold transition-all duration-200 border flex items-center gap-1.5"
                                            :class="selectedGroup === group.name 
                                                ? 'bg-[#f97316] text-white border-[#f97316] shadow-[0_0_12px_rgba(249,115,22,0.3)]' 
                                                : 'bg-[#1a1a1a] text-gray-400 border-[#333] hover:border-[#f97316]/50 hover:text-white'">
                                        <img x-show="getFlagUrl(group.name)" :src="getFlagUrl(group.name)" :alt="group.name" class="w-5 h-4 object-cover rounded-sm" loading="lazy">
                                        <i x-show="getFlagCode(group.name) === 'global'" class="fas fa-globe text-sm"></i>
                                        <span x-text="group.name"></span>
                                        <span class="ml-0.5 text-[10px] opacity-70" x-text="'(' + group.count + ')'"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Product List -->
                        <div x-show="filteredProducts.length > 0" class="grid grid-cols-3 md:grid-cols-3 gap-2 md:gap-4">
                            <template x-for="product in filteredProducts" :key="product.id">
                                <div class="border rounded-xl p-3 md:p-4 relative overflow-hidden group transition-all"
                                     :class="product.status !== 'available'
                                         ? 'border-[#333] bg-[#1a1a1a] opacity-50 cursor-not-allowed'
                                         : (selectedProduct === product.id
                                             ? 'border-[#f97316] bg-[#f97316]/5 cursor-pointer'
                                             : 'border-[#333] hover:border-[#f97316] bg-[#222] cursor-pointer')"
                                     @click="product.status === 'available' && selectProduct(product.id, product.name, product.price)">
                                    
                                    <div class="flex items-start gap-2 mb-3">
                                        <i class="fas fa-gem text-blue-400 mt-1" :class="product.status !== 'available' ? 'text-gray-600' : 'text-blue-400'"></i>
                                        <div class="text-xs md:text-sm font-bold leading-tight" :class="product.status !== 'available' ? 'text-gray-500' : 'text-gray-200 group-hover:text-white'" x-text="product.name"></div>
                                    </div>
                                    <div class="font-black text-sm md:text-base" :class="product.status !== 'available' ? 'text-gray-600' : 'text-[#f97316]'" x-text="'Rp ' + formatRupiah(product.price)">
                                    </div>

                                    <!-- Gangguan Badge -->
                                    <div x-show="product.status !== 'available'" class="absolute top-2 right-2">
                                        <span class="bg-red-500/20 text-red-400 text-[10px] font-bold px-2 py-0.5 rounded-full border border-red-500/30">Gangguan</span>
                                    </div>

                                    <!-- Checkmark Overlay -->
                                    <div class="absolute bottom-3 right-3 text-[#f97316] scale-0 transition-transform duration-200" 
                                         :class="{'scale-100': selectedProduct === product.id}">
                                        <i class="fas fa-check-circle text-lg bg-black rounded-full"></i>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Empty filtered state -->
                        <div x-show="filteredProducts.length === 0" class="text-center text-gray-500 py-8 bg-[#121212] rounded-lg border border-dashed border-[#333] hidden" :class="{'hidden': filteredProducts.length > 0}">
                            <p class="text-sm text-gray-400">Tidak ada produk dalam layanan ini.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. Masukkan Jumlah Pembelian -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="bg-[#f97316] text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">3</span>
                        <h2 class="text-base md:text-lg font-bold text-white">Masukkan Jumlah Pembelian</h2>
                    </div>
                </div>
                <div class="p-4 md:p-6 bg-[#1a1a1a]">
                    <div class="flex items-center justify-between bg-[#121212] border border-[#333] rounded-lg p-1 max-w-sm">
                        <button @click="if(quantity > 1) quantity--" class="w-10 h-10 flex items-center justify-center text-[#f97316] hover:bg-[#222] rounded-md transition font-bold" type="button"><i class="fas fa-minus"></i></button>
                        <input type="number" x-model="quantity" readonly class="w-16 bg-transparent text-center text-white font-bold text-lg border-none outline-none ring-0 focus:outline-none focus:ring-0 pointer-events-none appearance-none">
                        <button @click="quantity++" class="w-10 h-10 flex items-center justify-center text-[#f97316] hover:bg-[#222] rounded-md transition font-bold" type="button"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>

            <!-- 4. Pilih Pembayaran -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-[#f97316] text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">4</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Pilih Saluran Pembayaran</h2>
                </div>
                @php
                    $groupedChannels = collect($paymentChannels ?? [])->groupBy('group_key');
                    $firstGroupKey = $groupedChannels->keys()->first();
                @endphp
                <div class="p-4 md:p-6" x-data="{ activePayGroup: '{{ $firstGroupKey ?? '' }}' }">
                    @if($groupedChannels->isEmpty())
                        <div class="text-center text-gray-500 py-8 bg-[#121212] rounded-lg border border-dashed border-[#333]">
                            <p class="text-sm text-gray-400">Metode pembayaran belum dikonfigurasi.</p>
                        </div>
                    @else
                        <div class="text-sm text-gray-400 mb-4">Semua Saluran Pembayaran</div>
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                            <div class="lg:col-span-4 border border-[#333] rounded-xl bg-[#1a1a1a] overflow-hidden">
                                @auth
                                <button type="button"
                                        @click="activePayGroup = 'wallet'"
                                        class="w-full text-left px-4 py-3 border-b border-[#2a2a2a] last:border-b-0 transition"
                                        :class="activePayGroup === 'wallet' ? 'bg-[#f97316]/15 border-l-4 border-l-[#f97316]' : 'hover:bg-[#222]'">
                                    <div class="text-white font-bold text-sm">{{ \App\Models\Setting::get('wallet_label', 'Saldo') }}</div>
                                    <div class="text-[#f97316] text-xs font-semibold mt-1">Rp <span x-text="formatRupiah({{ auth()->user()->wallet_balance ?? 0 }})"></span></div>
                                </button>
                                @endauth
                                @foreach($groupedChannels as $groupKey => $channels)
                                <button type="button"
                                        @click="activePayGroup = '{{ $groupKey }}'"
                                        class="w-full text-left px-4 py-3 border-b border-[#2a2a2a] last:border-b-0 transition"
                                        :class="activePayGroup === '{{ $groupKey }}' ? 'bg-[#f97316]/15 border-l-4 border-l-[#f97316]' : 'hover:bg-[#222]'">
                                    <div class="text-white font-bold text-sm">{{ $channels->first()['group_label'] ?? 'Metode' }}</div>
                                    <div class="text-[#f97316] text-xs font-semibold mt-1">Rp <span x-text="formatRupiah(selectedPrice * quantity)"></span></div>
                                </button>
                                @endforeach
                            </div>
                            <div class="lg:col-span-8">
                                @auth
                                <div x-show="activePayGroup === 'wallet'" class="grid grid-cols-1 gap-3 mb-3 border-b border-[#2d2d2d] pb-3" style="display: none;" x-transition>
                                    <button type="button"
                                            class="text-left border border-[#333] hover:border-[#f97316] bg-[#222] rounded-xl p-3 transition-all flex justify-between items-center"
                                            :class="(selectedPayment === 'wallet') ? 'border-[#f97316] bg-[#f97316]/10' : ''"
                                            @click="selectPayment('wallet', '{{ \App\Models\Setting::get('wallet_label', 'Saldo') }}')">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center text-xl font-bold">
                                                <i class="fas fa-wallet"></i>
                                            </div>
                                            <div>
                                                <p class="text-white font-bold text-sm leading-tight">{{ \App\Models\Setting::get('wallet_label', 'Saldo') }}</p>
                                                <p class="text-gray-500 text-[11px]">Sisa: Rp <span x-text="formatRupiah({{ auth()->user()->wallet_balance ?? 0 }})"></span></p>
                                                <p class="text-[#f97316] text-sm font-black mt-1">Rp <span x-text="formatRupiah(selectedPrice * quantity)"></span></p>
                                            </div>
                                        </div>
                                        <div class="text-[#f97316] scale-0 transition-transform duration-200" :class="{'scale-100': selectedPayment === 'wallet'}">
                                            <i class="fas fa-check-circle text-xl bg-black rounded-full"></i>
                                        </div>
                                    </button>
                                </div>
                                @endauth
                                @foreach($groupedChannels as $groupKey => $channels)
                                <div x-show="activePayGroup === '{{ $groupKey }}'" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($channels as $channel)
                                    @php($channelDisplay = $channel['name'])
                                    <button type="button"
                                            class="text-left border border-[#333] hover:border-[#f97316] bg-[#222] rounded-xl p-3 transition-all"
                                            :class="(selectedPayment === {{ $channel['gateway_id'] }} && selectedPaymentName === @js($channelDisplay)) ? 'border-[#f97316] bg-[#f97316]/10' : ''"
                                            @click="selectPayment({{ $channel['gateway_id'] }}, @js($channelDisplay))">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-lg bg-white text-gray-800 flex items-center justify-center text-[10px] font-bold px-1 text-center">
                                                {{ strtoupper(substr($channel['name'], 0, 7)) }}
                                            </div>
                                            <div>
                                                <p class="text-white font-bold text-sm leading-tight">{{ $channel['name'] }}</p>
                                                <p class="text-[#f97316] text-sm font-black mt-1">Rp <span x-text="formatRupiah(selectedPrice * quantity)"></span></p>
                                            </div>
                                        </div>
                                    </button>
                                    @endforeach
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 5. Detail Kontak -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-[#f97316] text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">5</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Detail Kontak</h2>
                </div>
                <div class="p-4 md:p-6 space-y-4">
                    @guest
                    <a href="{{ route('login') }}" class="flex items-center gap-3 bg-[#f97316]/10 border border-[#f97316]/30 rounded-lg px-4 py-3 text-sm text-[#f97316] hover:bg-[#f97316]/20 transition mb-4">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span><strong>Login</strong> untuk auto-isi email & nomor WA kamu dengan instan!</span>
                        <i class="fas fa-chevron-right ml-auto text-xs"></i>
                    </a>
                    @endguest
                    <div>
                        <input type="email" x-model="email" placeholder="example@gmail.com" class="w-full bg-[#121212] border border-[#333] px-4 py-3 rounded-lg focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition text-white placeholder-gray-500 text-sm">
                    </div>
                    <div>
                        <div class="flex">
                            <span class="inline-flex items-center px-4 rounded-l-lg border border-r-0 border-[#333] bg-[#222] text-gray-400 text-sm">
                                +62
                            </span>
                            <input type="text" x-model="wa" placeholder="No. WhatsApp" class="w-full bg-[#121212] border border-[#333] px-4 py-3 rounded-r-lg focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition text-white placeholder-gray-500 text-sm">
                        </div>
                        <p class="text-[11px] text-gray-500 mt-2 italic">* Nomor ini akan dihubungi jika terjadi masalah dan untuk pengiriman bukti OTP/Struk</p>
                    </div>
                </div>
            </div>

            {{-- Promo section: hidden until promo system is built --}}

            <!-- Deskripsi Product Bawah -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden p-4 md:p-6 text-sm text-gray-300">
                <h3 class="font-bold text-white mb-3">Deskripsi {{ $category->name }}</h3>
                <div class="prose prose-sm prose-invert max-w-none">
                    @if($category->description)
                        {!! $category->description !!}
                    @else
                        <p>Cara top up {{ $category->name }} proses cepat instan dengan pembayaran 100% aman terlengkap:</p>
                        <ol class="list-decimal pl-5 space-y-1 mt-2 mb-4 text-gray-400">
                            <li>Pilih nominal</li>
                            <li>Masukkan data akun</li>
                            <li>Masukkan jumlah pembelian</li>
                            <li>Pilih pembayaran</li>
                            <li>Isi detail kontak</li>
                            <li>Masukkan kode promo (jika ada)</li>
                            <li>Klik order dan lakukan pembayaran</li>
                            <li>Selesai</li>
                        </ol>
                        <hr class="border-[#333] my-4">
                        <p class="text-xs text-gray-400">Pastikan ID dan server anda benar. Kesalahan input ID merupakan tanggung jawab pembeli sepenuhnya.</p>
                    @endif
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mt-2" x-data="{ activeFaq: null }">
                <h3 class="text-lg font-bold text-white mb-4">Kamu Punya Pertanyaan?</h3>
                <div class="space-y-2">
                    <!-- FAQ 1 -->
                    <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-lg overflow-hidden">
                        <button @click="activeFaq = activeFaq === 1 ? null : 1" class="w-full flex items-center justify-between p-4 text-left focus:outline-none">
                            <span class="text-sm font-semibold text-gray-200">Apakah aman melakukan top up di SINI?</span>
                            <i class="fas fa-chevron-down text-gray-500 transition-transform" :class="{'rotate-180': activeFaq === 1}"></i>
                        </button>
                        <div x-show="activeFaq === 1" x-collapse class="px-4 pb-4 text-xs text-gray-400">
                            Tentu saja aman 100% legal dan terpercaya. Kami bekerjasama langsung dengan provider resmi.
                        </div>
                    </div>
                    <!-- FAQ 2 -->
                    <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-lg overflow-hidden">
                        <button @click="activeFaq = activeFaq === 2 ? null : 2" class="w-full flex items-center justify-between p-4 text-left focus:outline-none">
                            <span class="text-sm font-semibold text-gray-200">Berapa lama item masuk ke akun saya?</span>
                            <i class="fas fa-chevron-down text-gray-500 transition-transform" :class="{'rotate-180': activeFaq === 2}"></i>
                        </button>
                        <div x-show="activeFaq === 2" x-collapse class="px-4 pb-4 text-xs text-gray-400">
                            Proses top up otomatis dan instan masuk dalam 1-3 detik setelah pembayaran berhasil dikonfirmasi oleh sistem.
                        </div>
                    </div>
                    <!-- FAQ 3 -->
                    <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-lg overflow-hidden">
                        <button @click="activeFaq = activeFaq === 3 ? null : 3" class="w-full flex items-center justify-between p-4 text-left focus:outline-none">
                            <span class="text-sm font-semibold text-gray-200">Metode pembayaran apa saja yang tersedia?</span>
                            <i class="fas fa-chevron-down text-gray-500 transition-transform" :class="{'rotate-180': activeFaq === 3}"></i>
                        </button>
                        <div x-show="activeFaq === 3" x-collapse class="px-4 pb-4 text-xs text-gray-400">
                            Kami menyediakan berbagai metode pembayaran mulai dari QRIS, E-Wallet (Dana, OVO, Gopay, ShopeePay), Virtual Account Bank, Mobile Banking, dan Alfamart/Indomaret.
                        </div>
                    </div>
                    <!-- FAQ 4 -->
                    <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-lg overflow-hidden">
                        <button @click="activeFaq = activeFaq === 4 ? null : 4" class="w-full flex items-center justify-between p-4 text-left focus:outline-none">
                            <span class="text-sm font-semibold text-gray-200">Bagaimana cara mengetahui ID saya?</span>
                            <i class="fas fa-chevron-down text-gray-500 transition-transform" :class="{'rotate-180': activeFaq === 4}"></i>
                        </button>
                        <div x-show="activeFaq === 4" x-collapse class="px-4 pb-4 text-xs text-gray-400">
                            Silakan buka profil di dalam game Anda, ID biasanya terletak di bawah nama profil berupa deretan angka unik.
                        </div>
                    </div>
                </div>
                
                @if($category->description)
                <div class="mt-6 bg-[#f97316]/10 border border-[#f97316] rounded-xl p-4 md:p-6 pb-2">
                    <p class="text-[11px] text-gray-300 leading-relaxed mb-2">
                        {!! nl2br(e($category->description)) !!}
                    </p>
                </div>
                @endif
            </div>

        </div>

        <!-- Right Col (Sidebar Review & Checkout) -->
        <div class="lg:col-span-4 flex flex-col gap-5 md:gap-6">
            
            {{-- Rating box: removed fake data, will be added back when review system is built --}}

            <!-- Bantuan Box -->
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\Setting::get('contact_whatsapp') ?? '') }}" target="_blank" class="block bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] p-4 flex items-center gap-4 group cursor-pointer hover:bg-[#222] transition">
                <div class="w-10 h-10 rounded-full bg-[#f97316]/10 flex items-center justify-center text-[#f97316]">
                    <i class="fas fa-headset text-xl group-hover:scale-110 transition-transform"></i>
                </div>
                <div>
                    <h4 class="text-white font-bold text-sm">Butuh Bantuan?</h4>
                    <p class="text-xs text-gray-400">Kamu bisa hubungi admin disini.</p>
                </div>
            </a>

            <!-- Checkout Summary Floating/Sticky -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden sticky top-24 shadow-2xl">
                <!-- If Unselected -->
                <div x-show="!selectedProduct || !selectedPayment" class="p-8 text-center" x-transition.opacity>
                    <p class="text-sm text-gray-500 my-4">Belum ada item produk yang dipilih.</p>
                    <button class="w-full bg-gray-700/50 text-gray-400 font-bold py-3 px-4 rounded-xl text-sm cursor-not-allowed border border-gray-600">
                        <i class="fas fa-lock mr-2"></i> Lengkapi Form
                    </button>
                </div>

                <!-- If Ready -->
                <div x-show="selectedProduct && selectedPayment" class="flex flex-col h-full" x-transition.opacity style="display: none;">
                    
                    <div class="p-5 flex-grow border-b border-[#2d2d2d]">
                        <h4 class="text-white font-black text-lg mb-4">Ringkasan Pesanan</h4>
                        
                        <div class="flex items-center gap-3 mb-4 p-3 bg-[#222] border border-[#333] rounded-lg">
                            <img src="{{ $category->thumbnail ? asset('storage/'.$category->thumbnail) : 'https://placehold.co/150' }}" class="w-12 h-12 rounded object-cover border border-[#444]">
                            <div>
                                <p class="text-white text-sm font-bold" x-text="selectedProductName">Product Name</p>
                                <p class="text-xs text-gray-400" x-text="'Jumlah: ' + quantity + 'x'">Jumlah: 1x</p>
                            </div>
                        </div>

                        <div class="space-y-2 text-sm mt-4">
                            <div x-show="validationResult && validationResult.success" class="flex justify-between text-gray-400">
                                <span>Username</span>
                                <span class="text-green-400 font-bold" x-text="validationResult?.nickname">-</span>
                            </div>
                            <div class="flex justify-between text-gray-400">
                                <span>Metode Pembayaran</span>
                                <span class="text-gray-300 font-semibold text-right" x-text="selectedPaymentName">-</span>
                            </div>
                            <div class="flex justify-between text-gray-400">
                                <span>Harga per item</span>
                                <span class="text-gray-300">Rp <span x-text="formatRupiah(selectedPrice)"></span></span>
                            </div>
                            <div class="flex justify-between text-gray-400">
                                <span>Kuantitas</span>
                                <span class="text-gray-300">x<span x-text="quantity"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 bg-[#151515]">
                        <div class="flex justify-between items-end mb-4">
                            <span class="text-gray-400 uppercase text-xs font-bold tracking-wider">Total Pembayaran</span>
                            <span class="text-[#f97316] font-black text-2xl">Rp <span x-text="formatRupiah(selectedPrice * quantity)"></span></span>
                        </div>
                        
                        <button 
                            @click="submitCheckout"
                            class="w-full bg-[#f97316] hover:bg-[#ea580c] text-white font-bold py-3.5 px-4 rounded-xl text-sm transition-all shadow-[0_0_20px_rgba(249,115,22,0.3)] hover:shadow-[0_0_25px_rgba(249,115,22,0.5)] flex items-center justify-center transform hover:-translate-y-0.5"
                            :disabled="!target || !wa || isSubmitting"
                            :class="{'opacity-70 cursor-not-allowed': !target || !wa || isSubmitting}">
                            <template x-if="!isSubmitting">
                                <span class="flex items-center">
                                    <i class="fas fa-shopping-bag mr-2"></i> Pesan Sekarang!
                                </span>
                            </template>
                            <template x-if="isSubmitting">
                                <span class="flex items-center">
                                    <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    Memproses...
                                </span>
                            </template>
                        </button>
                        
                        <!-- Validation warning inside checkout -->
                        <p x-show="!target || !wa" class="text-[10px] text-red-400 mt-2 text-center" x-transition>Harap isi Data Akun dan Whatsapp</p>
                    </div>
                </div>

            </div>
            
        </div>
    </div>
</div>

{{-- Reviews Section --}}
@if(isset($reviews))
<div class="container mx-auto px-4 mb-12">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center gap-3 mb-6">
            <i class="fas fa-star text-[#f97316] text-xl"></i>
            <h2 class="text-xl font-black text-white uppercase tracking-wider">Ulasan Pembeli</h2>
            @if($reviews->count() > 0)
            <span class="bg-[#f97316]/10 text-[#f97316] text-xs font-bold px-2 py-1 rounded-full">{{ $reviews->count() }}</span>
            @endif
        </div>
        @if($reviews->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($reviews as $review)
            <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-xl p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-[#f97316]/10 rounded-full flex items-center justify-center">
                            <span class="text-[#f97316] font-bold text-sm">{{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white">{{ $review->user->name ?? 'User' }}</p>
                            <p class="text-[10px] text-gray-500">{{ $review->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="flex gap-0.5">
                        @for($s = 1; $s <= 5; $s++)
                        <i class="fas fa-star text-xs {{ $s <= $review->rating ? 'text-yellow-400' : 'text-gray-700' }}"></i>
                        @endfor
                    </div>
                </div>
                @if($review->comment)
                <p class="text-sm text-gray-400 leading-relaxed">{{ $review->comment }}</p>
                @endif
                @if($review->product)
                <p class="text-[10px] text-gray-600 mt-2"><i class="fas fa-tag mr-1"></i>{{ $review->product->name }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-xl p-8 text-center">
            <i class="fas fa-comment-dots text-gray-600 text-3xl mb-3"></i>
            <p class="text-gray-500 text-sm">Belum ada ulasan. Jadi yang pertama memberikan ulasan!</p>
        </div>
        @endif
    </div>
</div>
@endif

@push('scripts')
<!-- Toast Notification -->
<div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none"></div>

<!-- Loading Overlay -->
<div id="checkout-overlay" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-[9998] flex flex-col items-center justify-center">
    <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-2xl p-8 flex flex-col items-center gap-4 shadow-2xl max-w-xs w-full mx-4">
        <svg class="animate-spin h-10 w-10 text-[#f97316]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <p class="text-white font-bold text-lg">Memproses Transaksi...</p>
        <p class="text-gray-400 text-sm text-center">Mohon tunggu, jangan tutup halaman ini.</p>
    </div>
</div>

<script>
    function showToast(type, title, message, duration = 5000) {
        const container = document.getElementById('toast-container');
        const id = 'toast-' + Date.now();
        const colors = {
            success: { bg: 'bg-green-900/90 border-green-500/50', icon: 'fa-check-circle text-green-400', title: 'text-green-300' },
            error:   { bg: 'bg-red-900/90 border-red-500/50',   icon: 'fa-times-circle text-red-400',   title: 'text-red-300'   },
            info:    { bg: 'bg-blue-900/90 border-blue-500/50', icon: 'fa-info-circle text-blue-400',   title: 'text-blue-300'  },
            warning: { bg: 'bg-yellow-900/90 border-yellow-500/50', icon: 'fa-exclamation-circle text-yellow-400', title: 'text-yellow-300' },
        };
        const c = colors[type] || colors.info;
        const el = document.createElement('div');
        el.id = id;
        el.className = `pointer-events-auto flex items-start gap-3 ${c.bg} border backdrop-blur-md rounded-xl p-4 shadow-2xl max-w-sm w-full transform translate-x-full opacity-0 transition-all duration-300`;
        el.innerHTML = `
            <i class="fas ${c.icon} text-xl mt-0.5 shrink-0"></i>
            <div class="flex-grow min-w-0">
                <p class="font-bold text-sm ${c.title}">${title}</p>
                ${message ? `<p class="text-gray-300 text-xs mt-1 leading-relaxed">${message}</p>` : ''}
            </div>
            <button onclick="dismissToast('${id}')" class="text-gray-500 hover:text-white transition shrink-0 ml-1">
                <i class="fas fa-times text-xs"></i>
            </button>
        `;
        container.appendChild(el);
        requestAnimationFrame(() => {
            el.classList.remove('translate-x-full', 'opacity-0');
        });
        if (duration > 0) {
            setTimeout(() => dismissToast(id), duration);
        }
        return id;
    }

    function dismissToast(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => el.remove(), 300);
    }

    function showOverlay() { document.getElementById('checkout-overlay').classList.remove('hidden'); }
    function hideOverlay() { document.getElementById('checkout-overlay').classList.add('hidden'); }

    document.addEventListener('alpine:init', () => {
        Alpine.data('checkoutPage', () => ({
            target: '',
            targetZone: '',
            email: '{{ auth()->user()->email ?? '' }}',
            wa: '{{ auth()->user()->whatsapp ?? '' }}',
            promo: '',
            isFavorite: {{ $isFav ? 'true' : 'false' }},
            isSubmitting: false,

            // Game ID Validation
            isValidating: false,
            validationResult: null,
            supportsIdValidation: {{ ($supportsIdValidation ?? false) ? 'true' : 'false' }},
            categoryName: @js($category->name),
            
            quantity: 1,
            selectedProduct: null,
            selectedProductName: '',
            selectedPrice: 0,

            preselectedProductId: {{ (int) ($preselectedProductId ?? 0) }},
            preselectedProductName: @js(optional($products->firstWhere('id', (int) ($preselectedProductId ?? 0)))->name ?? ''),
            preselectedProductPrice: {{ (float) (optional($products->firstWhere('id', (int) ($preselectedProductId ?? 0)))->price_sell ?? 0) }},
            
            selectedPayment: null,
            selectedPaymentName: '',

            allProducts: {!! json_encode($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->price_sell, 'type' => $p->product_type, 'group' => $p->product_group, 'status' => $p->status_provider ?? 'available'])->values()) !!},
            productGroups: @json($productGroups ?? []),
            hasGroups: {{ isset($hasGroups) && $hasGroups ? 'true' : 'false' }},
            productTypes: @json($productTypes ?? []),
            hasTypes: {{ isset($hasTypes) && $hasTypes ? 'true' : 'false' }},
            selectedType: null,
            selectedGroup: null,

            get hasSubGroups() {
                if (!this.hasTypes || !this.selectedType) return false;
                let typeObj = this.productTypes.find(t => t.name === this.selectedType);
                return typeObj ? typeObj.hasSubGroups : false;
            },

            get currentSubGroups() {
                if (!this.hasTypes || !this.selectedType) return [];
                let typeObj = this.productTypes.find(t => t.name === this.selectedType);
                return typeObj ? typeObj.subGroups : [];
            },

            get filteredProducts() {
                let list = this.allProducts;

                // Filter by type
                if (this.hasTypes && this.selectedType) {
                    list = list.filter(p => p.type === this.selectedType);
                } else if (this.hasTypes && !this.selectedType) {
                    return [];
                }

                // Filter by group
                if ((this.hasSubGroups || (!this.hasTypes && this.hasGroups)) && this.selectedGroup) {
                    list = list.filter(p => p.group === this.selectedGroup);
                } else if ((this.hasSubGroups || (!this.hasTypes && this.hasGroups)) && !this.selectedGroup) {
                    return [];
                }

                return list;
            },

            getFlagCode(name) {
                const map = {
                    'indonesia': 'id', 'malaysia': 'my', 'philippines': 'ph', 'filipina': 'ph',
                    'singapore': 'sg', 'singapura': 'sg', 'myanmar': 'mm', 'thailand': 'th',
                    'cambodia': 'kh', 'kamboja': 'kh', 'vietnam': 'vn',
                    'india': 'in', 'japan': 'jp', 'jepang': 'jp',
                    'korea': 'kr', 'south korea': 'kr', 'korea selatan': 'kr',
                    'brazil': 'br', 'brasil': 'br', 'usa': 'us', 'united states': 'us',
                    'europe': 'eu', 'eropa': 'eu', 'taiwan': 'tw', 'china': 'cn',
                    'russia': 'ru', 'rusia': 'ru', 'turkey': 'tr', 'turki': 'tr',
                    'bangladesh': 'bd', 'pakistan': 'pk', 'laos': 'la',
                    'brunei': 'bn', 'timor leste': 'tl', 'australia': 'au',
                    'mexico': 'mx', 'meksiko': 'mx', 'global': 'global', 'all': 'global',
                };
                const lower = (name || '').toLowerCase().trim();
                for (const [key, code] of Object.entries(map)) {
                    if (lower.includes(key)) return code;
                }
                return '';
            },

            getFlagUrl(name) {
                const code = this.getFlagCode(name);
                if (!code) return '';
                if (code === 'global') return '';
                return 'https://flagcdn.com/w40/' + code + '.png';
            },

            selectTypeLevel(typeName) {
                this.selectedType = typeName;
                this.selectedGroup = null;
                this.selectedProduct = null;
                this.selectedProductName = '';
                this.selectedPrice = 0;

                // Auto-select first sub-group if available
                if (this.hasSubGroups && this.currentSubGroups.length > 0) {
                    this.selectedGroup = this.currentSubGroups[0].name;
                }
            },

            selectGroup(groupName) {
                this.selectedGroup = groupName;
                this.selectedProduct = null;
                this.selectedProductName = '';
                this.selectedPrice = 0;
            },

            init() {
                // Pre-fill data dari URL query params (carry-over dari homepage widget)
                const urlParams = new URLSearchParams(window.location.search);
                const targetFromUrl = urlParams.get('target');
                if (targetFromUrl) {
                    this.target = targetFromUrl;
                }

                if (this.preselectedProductId > 0) {
                    const p = this.allProducts.find(x => parseInt(x.id) === parseInt(this.preselectedProductId));
                    if (p) {
                        if (p.type) this.selectedType = p.type;
                        if (p.group) this.selectedGroup = p.group;
                    }
                    this.selectProduct(this.preselectedProductId, this.preselectedProductName, this.preselectedProductPrice);
                } else {
                    // Auto-select first type or group
                    if (this.hasTypes && this.productTypes.length > 0) {
                        this.selectTypeLevel(this.productTypes[0].name);
                    } else if (this.hasGroups && this.productGroups.length > 0) {
                        this.selectGroup(this.productGroups[0].name);
                    }
                }
            },

            selectProduct(id, name, price) {
                this.selectedProduct = id;
                this.selectedProductName = name;
                this.selectedPrice = price;
                // Scroll if mobile to make it smooth, omitted for simplicity
            },

            selectPayment(id, name) {
                this.selectedPayment = id;
                this.selectedPaymentName = name;
            },

            toggleFavorite() {
                fetch('{{ route('member.favorites.toggle', $category->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        this.isFavorite = data.is_favorite;
                    }
                })
                .catch(err => console.error(err));
            },

            formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            },

            async validateGameId() {
                if (!this.target || this.isValidating) return;
                
                this.isValidating = true;
                this.validationResult = null;

                try {
                    const response = await fetch('{{ route('api.validate-game-id') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            category_name: this.categoryName,
                            user_id: this.target,
                            zone_id: this.targetZone || null
                        })
                    });

                    const data = await response.json();
                    this.validationResult = data;

                    if (data.success) {
                        showToast('success', 'ID Valid!', 'Username: ' + data.nickname);
                    } else {
                        showToast('error', 'ID Tidak Valid', data.message || 'ID tidak ditemukan.');
                    }
                } catch (error) {
                    console.error('Validation error:', error);
                    this.validationResult = {
                        success: false,
                        nickname: '',
                        message: 'Gagal menghubungi server. Periksa koneksi internet.'
                    };
                    showToast('error', 'Koneksi Gagal', 'Tidak dapat terhubung ke server validasi.');
                } finally {
                    this.isValidating = false;
                }
            },

            submitCheckout() {
                if (!this.target) {
                    showToast('warning', 'Data Akun Wajib Diisi', 'Mohon isi ID / Nomor Tujuan terlebih dahulu.');
                    return;
                }
                if (!this.wa) {
                    showToast('warning', 'WhatsApp Wajib Diisi', 'Mohon isi nomor WhatsApp pada form Detail Kontak.');
                    return;
                }
                if (!this.selectedPayment) {
                    showToast('warning', 'Pilih Metode Pembayaran', 'Mohon pilih metode pembayaran terlebih dahulu.');
                    return;
                }

                this.isSubmitting = true;
                showOverlay();

                fetch('{{ route('checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        customer_email: this.email,
                        customer_whatsapp: this.wa,
                        target_id: this.target,
                        target_zone: this.targetZone,
                        product_id: this.selectedProduct,
                        payment_method: this.selectedPayment,
                        quantity: this.quantity
                    })
                })
                .then(response => {
                    if (!response.ok && response.status === 422) {
                        return response.json().then(d => {
                            let msg = d.message || '';
                            if (d.errors) {
                                msg = Object.values(d.errors).flat().join(' ');
                            }
                            return { success: false, message: msg };
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    hideOverlay();
                    if (data.success) {
                        showToast('success', 'Pesanan Berhasil Dibuat!', 'Mengarahkan ke halaman pembayaran...', 3000);
                        setTimeout(() => { window.location.href = data.redirect_url; }, 1200);
                    } else {
                        this.isSubmitting = false;
                        showToast('error', 'Transaksi Gagal', data.message || 'Terjadi kesalahan. Silakan coba lagi.');
                    }
                })
                .catch(error => {
                    hideOverlay();
                    this.isSubmitting = false;
                    console.error('Checkout error:', error);
                    showToast('error', 'Koneksi Gagal', 'Tidak dapat terhubung ke server. Periksa koneksi internet kamu.');
                });
            }
        }));
    });
</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #1a1a1a; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #444; }
</style>
@endpush
@endsection
