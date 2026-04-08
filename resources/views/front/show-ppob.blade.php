@extends('layouts.front')
@php
    $isFav = auth()->check() && \App\Models\FavoriteGame::where('user_id', auth()->id())->where('category_id', $category->id)->exists();
@endphp
@section('title', $category->name . ' - Bayar dan Tagihan')
@section('meta_description', 'Bayar tagihan ' . $category->name . ' cepat, aman, dan otomatis.')
@section('canonical', route('front.category', $category->slug ?? $category->id))

@push('jsonld')
<script type="application/ld+json">
{
    "{{ '@' }}context": "https://schema.org",
    "{{ '@' }}type": "WebPage",
    "name": "{{ $category->name }} - Bayar dan Tagihan",
    "url": "{{ route('front.category', $category->slug ?? $category->id) }}",
    "description": "Bayar tagihan {{ $category->name }} cepat, aman, dan otomatis."
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
    $ppobProductsSchema = [
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
                    'offers' => [
                        '@type' => 'Offer',
                        'url' => route('front.category', $category->slug ?? $category->id),
                        'priceCurrency' => 'IDR',
                        'price' => (float) $product->price_sell,
                        'availability' => 'https://schema.org/InStock',
                    ],
                ],
            ];
        })->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode($ppobProductsSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
@section('content')
<div class="container mx-auto px-4 pt-6 md:pt-10 relative z-20">
    <nav class="text-sm text-gray-400 mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li><a href="{{ route('front.index') }}" class="hover:text-white transition">Beranda</a></li>
            <li><i class="fas fa-chevron-right text-[10px] text-gray-500"></i></li>
            <li class="text-[#f97316] font-medium">{{ $category->name }}</li>
        </ol>
    </nav>
</div>

<div class="container mx-auto px-4 pb-20" x-data="ppobCheckout(
    {{ json_encode($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => $p->price_sell, 'type' => $p->product_type, 'group' => $p->product_group, 'status' => $p->status_provider ?? 'available'])) }}, 
    {{ json_encode($paymentGateways->map(fn($pg) => ['id' => $pg->id, 'name' => $pg->display_name ?? $pg->name, 'provider' => $pg->name, 'methods' => $pg->customer_methods ?? []])) }},
    {{ json_encode($productGroups ?? []) }},
    {{ isset($hasGroups) && $hasGroups ? 'true' : 'false' }},
    {{ json_encode($productTypes ?? []) }},
    {{ isset($hasTypes) && $hasTypes ? 'true' : 'false' }},
    {{ isset($isPostpaid) && $isPostpaid ? 'true' : 'false' }}
)">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <div class="lg:col-span-8 flex flex-col gap-5 md:gap-6">

            <!-- Category Header -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] p-5 md:p-6">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 md:w-16 md:h-16 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white text-2xl shadow-lg shrink-0">
                        <i class="{{ $category->icon ?? 'fas fa-file-invoice-dollar' }}"></i>
                    </div>
                    <div class="flex-grow flex justify-between items-center">
                        <div>
                            <h1 class="text-xl md:text-2xl font-black text-white">{{ $category->name }}</h1>
                            <p class="text-gray-400 text-sm mt-0.5">{{ $category->description ?? 'Bayar tagihan cepat dan aman' }}</p>
                        </div>
                        @auth
                        <button @click="toggleFavorite" type="button" class="ml-4 bg-[#222] hover:bg-[#2d2d2d] border border-[#333] w-10 h-10 rounded-full flex items-center justify-center transition shadow-lg shrink-0" :class="isFavorite ? 'text-rose-500' : 'text-gray-400'" title="Tambah ke Favorit">
                            <i class="fas fa-heart text-lg" :class="isFavorite ? 'fa-solid' : 'fa-regular'"></i>
                        </button>
                        @else
                        <a href="{{ route('login') }}" class="ml-4 bg-[#222] hover:bg-rose-900/30 border border-[#333] hover:border-rose-500/40 w-10 h-10 rounded-full flex items-center justify-center transition shadow-lg shrink-0 text-gray-500" title="Login untuk Simpan Favorit">
                            <i class="fas fa-heart text-lg"></i>
                        </a>
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Step 1: Input Data -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-green-500 text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">1</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Masukkan Data</h2>
                </div>
                <div class="p-4 md:p-6">
                    @foreach($formFields as $field)
                    <div class="mb-4 last:mb-0">
                        <label class="block text-gray-400 text-xs mb-1.5 font-medium">{{ $field['label'] ?? 'Field' }}</label>
                        <div class="relative">
                            <input type="text"
                                   x-model="target"
                                   @input="onTargetInput()"
                                   placeholder="{{ $field['placeholder'] ?? '' }}"
                                   class="w-full bg-[#121212] border border-[#333] px-4 py-3 rounded-lg focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition text-white placeholder-gray-500 text-sm"
                                   :class="detectedProvider ? 'pr-36' : ''">

                            <!-- Provider Badge (auto-detect) -->
                            <div x-show="detectedProvider" x-transition
                                 class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2 bg-[#222] border border-[#444] rounded-lg px-3 py-1.5">
                                <img x-show="providerLogo" :src="providerLogo" :alt="detectedProvider" class="h-5 w-auto object-contain" loading="lazy">
                                <span class="text-white text-xs font-bold" x-text="detectedProvider"></span>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Cek Tagihan Button (postpaid only) -->
                    <template x-if="isPostpaidMode">
                        <div class="mt-4">
                            <button @click="cekTagihan()"
                                    :disabled="!target || target.length < 4 || isInquiryLoading"
                                    :class="(!target || target.length < 4 || isInquiryLoading)
                                        ? 'bg-[#333] text-gray-500 cursor-not-allowed'
                                        : 'bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white'"
                                    class="w-full py-3 rounded-lg font-bold text-sm transition shadow-lg">
                                <span x-show="!isInquiryLoading"><i class="fas fa-search mr-2"></i>Cek Tagihan</span>
                                <span x-show="isInquiryLoading"><i class="fas fa-spinner fa-spin mr-2"></i>Mengecek Tagihan...</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Step 1.5: Hasil Cek Tagihan (postpaid only — shown after inquiry success) -->
            <template x-if="isPostpaidMode && inquiryResult">
                <div class="bg-[#1c1c1c] rounded-xl border overflow-hidden transition-all duration-300"
                     :class="inquiryResult.success ? 'border-green-500/50' : 'border-red-500/50'">
                    <div class="px-4 py-3 md:p-4 border-b flex items-center gap-3"
                         :class="inquiryResult.success ? 'bg-green-500/10 border-green-500/30' : 'bg-red-500/10 border-red-500/30'">
                        <span class="w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md"
                              :class="inquiryResult.success ? 'bg-green-500 text-white' : 'bg-red-500 text-white'">
                            <i :class="inquiryResult.success ? 'fas fa-check' : 'fas fa-times'"></i>
                        </span>
                        <h2 class="text-base md:text-lg font-bold text-white">Hasil Cek Tagihan</h2>
                    </div>
                    <div class="p-4 md:p-6">
                        <!-- Success result -->
                        <template x-if="inquiryResult.success">
                            <div>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center bg-[#121212] rounded-lg px-4 py-3">
                                        <span class="text-gray-400 text-sm">Nama Pelanggan</span>
                                        <span class="text-white font-bold text-sm" x-text="inquiryResult.customer_name"></span>
                                    </div>
                                    <div class="flex justify-between items-center bg-[#121212] rounded-lg px-4 py-3">
                                        <span class="text-gray-400 text-sm">No. Pelanggan</span>
                                        <span class="text-white font-medium text-sm" x-text="inquiryResult.customer_no"></span>
                                    </div>
                                    <template x-if="inquiryResult.periode">
                                        <div class="flex justify-between items-center bg-[#121212] rounded-lg px-4 py-3">
                                            <span class="text-gray-400 text-sm">Periode</span>
                                            <span class="text-white font-medium text-sm" x-text="inquiryResult.periode"></span>
                                        </div>
                                    </template>
                                    <template x-if="inquiryResult.admin > 0">
                                        <div class="flex justify-between items-center bg-[#121212] rounded-lg px-4 py-3">
                                            <span class="text-gray-400 text-sm">Biaya Admin</span>
                                            <span class="text-gray-300 font-medium text-sm" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(inquiryResult.admin)"></span>
                                        </div>
                                    </template>

                                    <!-- Detail tagihan (PLN, PDAM, dsb) -->
                                    <template x-if="inquiryResult.desc && inquiryResult.desc.detail && inquiryResult.desc.detail.length > 0">
                                        <div class="bg-[#121212] rounded-lg p-4">
                                            <p class="text-gray-400 text-xs font-medium mb-2">Detail Tagihan</p>
                                            <template x-for="(d, idx) in inquiryResult.desc.detail" :key="idx">
                                                <div class="flex justify-between items-center py-1.5 border-b border-[#222] last:border-0">
                                                    <span class="text-gray-300 text-xs" x-text="'Periode: ' + (d.periode || '-')"></span>
                                                    <span class="text-white text-xs font-bold" x-text="d.nilai_tagihan ? 'Rp ' + new Intl.NumberFormat('id-ID').format(d.nilai_tagihan) : '-'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Extra desc info (tarif, daya, alamat, etc) -->
                                    <template x-if="inquiryResult.desc && (inquiryResult.desc.tarif || inquiryResult.desc.daya || inquiryResult.desc.alamat)">
                                        <div class="bg-[#121212] rounded-lg p-4">
                                            <p class="text-gray-400 text-xs font-medium mb-2">Info Tambahan</p>
                                            <div class="space-y-1 text-xs">
                                                <template x-if="inquiryResult.desc.tarif">
                                                    <div class="flex justify-between"><span class="text-gray-400">Tarif</span><span class="text-gray-300" x-text="inquiryResult.desc.tarif"></span></div>
                                                </template>
                                                <template x-if="inquiryResult.desc.daya">
                                                    <div class="flex justify-between"><span class="text-gray-400">Daya</span><span class="text-gray-300" x-text="inquiryResult.desc.daya + ' VA'"></span></div>
                                                </template>
                                                <template x-if="inquiryResult.desc.alamat">
                                                    <div class="flex justify-between"><span class="text-gray-400">Alamat</span><span class="text-gray-300" x-text="inquiryResult.desc.alamat"></span></div>
                                                </template>
                                                <template x-if="inquiryResult.desc.lembar_tagihan">
                                                    <div class="flex justify-between"><span class="text-gray-400">Lembar Tagihan</span><span class="text-gray-300" x-text="inquiryResult.desc.lembar_tagihan"></span></div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <div class="flex justify-between items-center bg-green-500/10 border border-green-500/30 rounded-lg px-4 py-3">
                                        <span class="text-green-400 font-bold text-sm">Total Tagihan</span>
                                        <span class="text-green-400 font-black text-lg" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(inquiryResult.price)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Failed result -->
                        <template x-if="!inquiryResult.success">
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-triangle text-3xl text-red-400 mb-3"></i>
                                <p class="text-red-400 font-bold text-sm" x-text="inquiryResult.message || 'Tagihan tidak ditemukan'"></p>
                                <p class="text-gray-500 text-xs mt-1">Periksa kembali nomor pelanggan dan coba lagi.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Step 2: Pilih Produk (Filtered by Provider) — for postpaid, only after inquiry -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden"
                 x-show="!isPostpaidMode || (isPostpaidMode && inquiryResult && inquiryResult.success)">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-green-500 text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">2</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Pilih Layanan</h2>
                    <span x-show="detectedProvider && !providerMismatch" x-transition class="text-xs text-green-400 font-medium ml-auto" x-text="'Menampilkan produk ' + detectedProvider"></span>
                    <span x-show="detectedProvider && providerMismatch" x-transition class="text-xs text-yellow-400 font-medium ml-auto"><i class="fas fa-exclamation-triangle mr-1"></i> Provider tidak sesuai</span>
                </div>
                <div class="p-4 md:p-6">
                    <!-- Level 1: Type Tabs (inline pills) -->
                    <div x-show="hasTypes" class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="type in productTypes" :key="type.name">
                                <button type="button" @click="selectTypeLevel(type.name)"
                                    class="px-4 py-2 rounded-full text-sm font-bold transition-all duration-200 border flex items-center gap-1.5"
                                    :class="selectedType === type.name
                                        ? 'text-white border-emerald-500 shadow-lg shadow-emerald-500/20'
                                        : 'text-gray-300 border-[#333] hover:border-emerald-500/50 hover:text-white'"
                                    :style="selectedType === type.name ? 'background-color: #10b981;' : 'background-color: #1a1a1a;'">
                                    <img x-show="getFlagUrl(type.name)" :src="getFlagUrl(type.name)" :alt="type.name" class="w-5 h-4 object-cover rounded-sm" loading="lazy">
                                    <i x-show="getFlagCode(type.name) === 'global'" class="fas fa-globe text-sm"></i>
                                    <span x-text="type.name"></span>
                                    <span class="ml-0.5 text-[10px] opacity-70" x-text="'(' + type.count + ')'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Level 2: Sub-group Tabs (when type has sub-groups) -->
                    <div x-show="hasTypes && selectedType && hasSubGroups" class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="group in currentSubGroups" :key="group.name">
                                <button type="button" @click="selectGroup(group.name)"
                                    class="px-3 py-1.5 rounded-full text-xs font-bold transition-all duration-200 border flex items-center gap-1"
                                    :class="selectedGroup === group.name
                                        ? 'text-white border-emerald-500 shadow-lg shadow-emerald-500/20'
                                        : 'text-gray-400 border-[#333] hover:border-emerald-500/50 hover:text-white'"
                                    :style="selectedGroup === group.name ? 'background-color: #10b981;' : 'background-color: #1a1a1a;'">
                                    <img x-show="getFlagUrl(group.name)" :src="getFlagUrl(group.name)" :alt="group.name" class="w-4 h-3 object-cover rounded-sm" loading="lazy">
                                    <i x-show="getFlagCode(group.name) === 'global'" class="fas fa-globe text-xs"></i>
                                    <span x-text="group.name"></span>
                                    <span class="ml-0.5 text-[10px] opacity-60" x-text="'(' + group.count + ')'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Level 2: Group-only Tabs (when no types, just groups) -->
                    <div x-show="!hasTypes && hasGroups" class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="group in productGroups" :key="group.name">
                                <button type="button" @click="selectGroup(group.name)"
                                    class="px-4 py-2 rounded-full text-sm font-bold transition-all duration-200 border flex items-center gap-1.5"
                                    :class="selectedGroup === group.name
                                        ? 'text-white border-emerald-500 shadow-lg shadow-emerald-500/20'
                                        : 'text-gray-300 border-[#333] hover:border-emerald-500/50 hover:text-white'"
                                    :style="selectedGroup === group.name ? 'background-color: #10b981;' : 'background-color: #1a1a1a;'">
                                    <img x-show="getFlagUrl(group.name)" :src="getFlagUrl(group.name)" :alt="group.name" class="w-5 h-4 object-cover rounded-sm" loading="lazy">
                                    <i x-show="getFlagCode(group.name) === 'global'" class="fas fa-globe text-sm"></i>
                                    <span x-text="group.name"></span>
                                    <span class="ml-0.5 text-[10px] opacity-70" x-text="'(' + group.count + ')'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Provider mismatch warning -->
                    <div x-show="isPulsaMode && detectedProvider && providerMismatch" class="text-center py-6 bg-[#121212] rounded-lg border border-dashed border-yellow-600/50">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2 text-yellow-500"></i>
                        <p class="text-sm text-yellow-400 font-medium">Nomor ini terdeteksi sebagai <span x-text="detectedProvider" class="font-bold"></span>.</p>
                        <p class="text-xs text-gray-400 mt-1">Anda sedang di halaman <span class="font-bold text-white">{{ $category->name }}</span>.</p>
                        <a :href="'/kategori/' + (PROVIDER_SLUGS[detectedProvider] || (detectedProvider ? detectedProvider.toLowerCase() : ''))" class="inline-block mt-3 px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-xs font-bold rounded-lg transition">
                            <i class="fas fa-arrow-right mr-1"></i> Ke Halaman <span x-text="detectedProvider"></span>
                        </a>
                    </div>

                    <!-- Prompt to enter number first (pulsa mode) -->
                    <div x-show="isPulsaMode && !detectedProvider && target.length < 4" class="text-center py-6 bg-[#121212] rounded-lg border border-dashed border-[#333]">
                        <i class="fas fa-mobile-alt text-2xl mb-2 text-gray-600"></i>
                        <p class="text-sm text-gray-400">Masukkan nomor HP di atas untuk menampilkan produk.</p>
                    </div>

                    <!-- Not detected -->
                    <div x-show="isPulsaMode && !detectedProvider && target.length >= 4" class="text-center py-6 bg-[#121212] rounded-lg border border-dashed border-red-900/50">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2 text-red-500/50"></i>
                        <p class="text-sm text-red-400">Provider tidak terdeteksi. Periksa kembali nomor HP Anda.</p>
                    </div>

                    <!-- Product Grid -->
                    <div x-show="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div class="border rounded-xl p-3 md:p-4 relative overflow-hidden group transition-all"
                                 :class="product.status !== 'available'
                                     ? 'border-[#333] bg-[#1a1a1a] opacity-50 cursor-not-allowed'
                                     : (selectedProduct === product.id
                                         ? 'border-green-500 bg-green-500/5 cursor-pointer'
                                         : 'border-[#333] hover:border-green-500 bg-[#222] cursor-pointer')"
                                 @click="product.status === 'available' && selectProduct(product.id, product.name, product.price)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs md:text-sm font-bold leading-tight" :class="product.status !== 'available' ? 'text-gray-500' : 'text-gray-200 group-hover:text-white'" x-text="product.name"></div>
                                        <div class="font-black text-sm md:text-base mt-1" :class="product.status !== 'available' ? 'text-gray-600' : 'text-green-400'" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(product.price)"></div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <!-- Gangguan Badge -->
                                        <span x-show="product.status !== 'available'" class="bg-red-500/20 text-red-400 text-[10px] font-bold px-2 py-0.5 rounded-full border border-red-500/30">Gangguan</span>
                                        <!-- Checkmark -->
                                        <div class="text-green-500 scale-0 transition-transform duration-200"
                                             :class="{'scale-100': selectedProduct === product.id}">
                                            <i class="fas fa-check-circle text-lg bg-black rounded-full"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- No products for non-pulsa -->
                    <div x-show="!isPulsaMode && filteredProducts.length === 0" class="text-center py-8 bg-[#121212] rounded-lg border border-dashed border-[#333]">
                        <i class="fas fa-box-open text-3xl mb-2 text-gray-600"></i>
                        <p class="text-sm text-gray-400">Belum ada layanan tersedia.</p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Pilih Pembayaran -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-green-500 text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">3</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Pilih Pembayaran</h2>
                </div>
                <div class="p-4 md:p-6">
                    <div x-show="allPaymentGateways.length > 0 || {{ auth()->check() ? 'true' : 'false' }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @auth
                        <div class="border border-[#333] hover:border-green-500 bg-[#222] rounded-xl p-3 md:p-4 cursor-pointer relative group transition-all"
                             :class="{'border-green-500 bg-green-500/5': selectedPayment === 'wallet'}"
                             @click="selectPayment('wallet', '{{ \App\Models\Setting::get('wallet_label', 'Saldo') }}')">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white text-sm font-bold"><i class="fas fa-wallet"></i></div>
                                <div>
                                    <p class="text-gray-200 font-bold text-sm">{{ \App\Models\Setting::get('wallet_label', 'Saldo') }}</p>
                                    <p class="text-gray-500 text-[11px]">Sisa Saldo: Rp <span x-text="new Intl.NumberFormat('id-ID').format({{ auth()->user()->wallet_balance ?? 0 }})"></span></p>
                                </div>
                            </div>
                            <div class="absolute top-3 right-3 text-green-500 scale-0 transition-transform duration-200"
                                 :class="{'scale-100': selectedPayment === 'wallet'}">
                                <i class="fas fa-check-circle text-lg"></i>
                            </div>
                        </div>
                        @endauth
                        <template x-for="pg in allPaymentGateways" :key="pg.id">
                            <div class="border border-[#333] hover:border-green-500 bg-[#222] rounded-xl p-3 md:p-4 cursor-pointer relative group transition-all"
                                 :class="{'border-green-500 bg-green-500/5': selectedPayment === pg.id}"
                                 @click="selectPayment(pg.id, pg.name)">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white text-xs font-bold" x-text="pg.name.substring(0,2)"></div>
                                    <div>
                                        <p class="text-gray-200 font-bold text-sm" x-text="pg.name"></p>
                                        <p class="text-gray-500 text-[11px]" x-show="pg.methods && pg.methods.length > 1" x-text="pg.methods.join(' • ')"></p>
                                    </div>
                                </div>
                                <div class="absolute top-3 right-3 text-green-500 scale-0 transition-transform duration-200"
                                     :class="{'scale-100': selectedPayment === pg.id}">
                                    <i class="fas fa-check-circle text-lg"></i>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div x-show="allPaymentGateways.length === 0" class="text-center text-gray-500 py-8 bg-[#121212] rounded-lg border border-dashed border-[#333]">
                        <p class="text-sm text-gray-400">Metode pembayaran belum dikonfigurasi.</p>
                    </div>
                </div>
            </div>

            <!-- Step 4: Kontak -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-green-500 text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">4</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Kontak</h2>
                </div>
                <div class="p-4 md:p-6">
                    @guest
                    <a href="{{ route('login') }}" class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 rounded-lg px-4 py-3 text-sm text-green-400 hover:bg-green-500/20 transition mb-4">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span><strong>Login</strong> untuk auto-isi nomor WA & email kamu dengan instan!</span>
                        <i class="fas fa-chevron-right ml-auto text-xs"></i>
                    </a>
                    @endguest
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-400 text-xs mb-1.5 font-medium">WhatsApp</label>
                            <input type="text" x-model="whatsapp" placeholder="08xxxxxxxxxx" class="w-full bg-[#121212] border border-[#333] px-4 py-3 rounded-lg focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition text-white placeholder-gray-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-xs mb-1.5 font-medium">Email (Opsional)</label>
                            <input type="email" x-model="email" placeholder="email@example.com" class="w-full bg-[#121212] border border-[#333] px-4 py-3 rounded-lg focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500 transition text-white placeholder-gray-500 text-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Col: Summary -->
        <div class="lg:col-span-4">
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] p-5 md:p-6 sticky top-28">
                <h3 class="text-white text-lg font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-receipt text-green-400"></i> Ringkasan
                </h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-400">
                        <span>Layanan</span>
                        <span class="text-white font-medium" x-text="selectedProductName || '-'"></span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Nomor Tujuan</span>
                        <span class="text-white font-medium" x-text="target || '-'"></span>
                    </div>
                    <template x-if="isPostpaidMode && inquiryResult && inquiryResult.success">
                        <div class="flex justify-between text-gray-400">
                            <span>Nama Pelanggan</span>
                            <span class="text-green-400 font-bold" x-text="inquiryResult.customer_name"></span>
                        </div>
                    </template>
                    <div x-show="detectedProvider" class="flex justify-between items-center text-gray-400">
                        <span>Provider</span>
                        <div class="flex items-center gap-2">
                            <img x-show="providerLogo" :src="providerLogo" :alt="detectedProvider" class="h-4 w-auto object-contain" loading="lazy">
                            <span class="text-green-400 font-bold" x-text="detectedProvider"></span>
                        </div>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Harga</span>
                        <span class="text-white font-bold" x-text="displayPrice ? 'Rp ' + new Intl.NumberFormat('id-ID').format(displayPrice) : '-'"></span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Pembayaran</span>
                        <span class="text-white font-medium" x-text="selectedPaymentName || '-'"></span>
                    </div>
                </div>
                <div class="border-t border-[#2d2d2d] mt-4 pt-4">
                    <div class="flex justify-between items-end text-lg">
                        <span class="text-gray-400 font-medium">Total</span>
                        <span class="text-green-400 font-black" x-text="displayPrice ? 'Rp ' + new Intl.NumberFormat('id-ID').format(displayPrice) : '-'"></span>
                    </div>
                </div>
                <button @click="submitCheckout()"
                        :disabled="!canSubmit"
                        :class="canSubmit ? 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white' : 'bg-[#333] text-gray-500 cursor-not-allowed'"
                        class="w-full mt-5 py-3 rounded-lg font-bold text-sm transition shadow-lg">
                    <span x-show="!isSubmitting"><i class="fas fa-paper-plane mr-2"></i>Bayar Sekarang</span>
                    <span x-show="isSubmitting"><i class="fas fa-spinner fa-spin mr-2"></i>Memproses...</span>
                </button>
                <p class="text-center text-gray-500 text-[11px] mt-3">
                    <i class="fas fa-lock mr-1"></i> Pembayaran dijamin aman
                </p>
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

<div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none"></div>

<div id="checkout-overlay" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-[9998] flex flex-col items-center justify-center">
    <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-2xl p-8 flex flex-col items-center gap-4 shadow-2xl max-w-xs w-full mx-4">
        <svg class="animate-spin h-10 w-10 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <p class="text-white font-bold text-lg">Memproses Pembayaran...</p>
        <p class="text-gray-400 text-sm text-center">Mohon tunggu, jangan tutup halaman ini.</p>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #1a1a1a; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #444; }
</style>

<script>
const PROVIDER_PREFIXES = {
    'Telkomsel': ['0811','0812','0813','0821','0822','0823','0852','0853'],
    'Indosat': ['0814','0815','0816','0855','0856','0857','0858'],
    'XL': ['0817','0818','0819','0859','0877','0878'],
    'Axis': ['0831','0832','0833','0838'],
    'Tri': ['0895','0896','0897','0898','0899'],
    'Smartfren': ['0881','0882','0883','0884','0885','0886','0887','0888','0889'],
    'By.U': ['0851'],
};

const PROVIDER_LOGOS = {
    'Telkomsel': 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Logo_of_Telkomsel_%282021%29.svg/200px-Logo_of_Telkomsel_%282021%29.svg.png',
    'Indosat': 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d2/Logo_of_Indosat_Ooredoo_Hutchison.svg/200px-Logo_of_Indosat_Ooredoo_Hutchison.svg.png',
    'XL': 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Logo_of_XL_Axiata.svg/200px-Logo_of_XL_Axiata.svg.png',
    'Axis': 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0e/Logo_of_Axis_Telekom_Indonesia.svg/200px-Logo_of_Axis_Telekom_Indonesia.svg.png',
    'Tri': 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/3_%28telecommunications%29_logo_2.svg/200px-3_%28telecommunications%29_logo_2.svg.png',
    'Smartfren': 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Logo_of_Smartfren.svg/200px-Logo_of_Smartfren.svg.png',
    'By.U': 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/By.U_logo.svg/200px-By.U_logo.svg.png',
};

const PROVIDER_COLORS = {
    'Telkomsel': 'bg-red-600',
    'Indosat': 'bg-yellow-500',
    'XL': 'bg-blue-600',
    'Axis': 'bg-purple-600',
    'Tri': 'bg-orange-500',
    'Smartfren': 'bg-pink-600',
    'By.U': 'bg-violet-500',
};

const CATEGORY_TYPE = '{{ $category->type ?? "ppob" }}';
const PULSA_TYPES = ['pulsa', 'paket_data'];
const PROVIDER_SLUGS = {
    'Telkomsel': 'telkomsel', 'Indosat': 'indosat', 'XL': 'xl',
    'Axis': 'axis', 'Tri': 'tri', 'Smartfren': 'smartfren', 'By.U': 'byu',
};

function detectProvider(number) {
    const clean = number.replace(/\D/g, '');
    if (clean.length < 4) return null;
    const prefix4 = clean.substring(0, 4);
    for (const [provider, prefixes] of Object.entries(PROVIDER_PREFIXES)) {
        if (prefixes.includes(prefix4)) return provider;
    }
    return null;
}

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
    requestAnimationFrame(() => { el.classList.remove('translate-x-full', 'opacity-0'); });
    if (duration > 0) setTimeout(() => dismissToast(id), duration);
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
    Alpine.data('ppobCheckout', (allProducts, allPGs, productGroups, hasGroups, productTypes, hasTypes, isPostpaid) => ({
        target: '',
        targetZone: '',
        whatsapp: '{{ auth()->user()->whatsapp ?? '' }}',
        email: '{{ auth()->user()->email ?? '' }}',
        isFavorite: {{ $isFav ? 'true' : 'false' }},
        detectedProvider: null,
        providerColor: '',
        providerLogo: '',
        isPulsaMode: PULSA_TYPES.includes(CATEGORY_TYPE),
        isPostpaidMode: isPostpaid || false,
        categoryName: '{{ $category->name ?? '' }}',
        allProducts: allProducts,
        allPaymentGateways: allPGs,
        productGroups: productGroups || [],
        hasGroups: hasGroups || false,
        productTypes: productTypes || [],
        hasTypes: hasTypes || false,
        selectedType: null,
        selectedGroup: null,

        selectedProduct: null,
        selectedProductName: '',
        selectedProductPrice: 0,

        // Postpaid inquiry state
        isInquiryLoading: false,
        inquiryResult: null,
        inquiryRefId: null,

        preselectedProductId: {{ (int) ($preselectedProductId ?? 0) }},
        preselectedProductName: @js(optional($products->firstWhere('id', (int) ($preselectedProductId ?? 0)))->name ?? ''),
        preselectedProductPrice: {{ (float) (optional($products->firstWhere('id', (int) ($preselectedProductId ?? 0)))->price_sell ?? 0) }},
        selectedPayment: null,
        selectedPaymentName: '',
        isSubmitting: false,

        // Check if selected type has sub-groups
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

        init() {
            const params = new URLSearchParams(window.location.search);
            
            if (params.has('target')) {
                this.target = params.get('target');
                this.onTargetInput();
            }

            if (params.has('product')) {
                const pid = parseInt(params.get('product'));
                const p = this.allProducts.find(x => parseInt(x.id) === pid);
                if (p) {
                    if (p.type) this.selectedType = p.type;
                    if (p.group) this.selectedGroup = p.group;
                    this.selectProduct(p.id, p.name, p.price);
                }
            } else if (this.preselectedProductId > 0) {
                const p = this.allProducts.find(x => parseInt(x.id) === parseInt(this.preselectedProductId));
                if (p) {
                    if (p.type) this.selectedType = p.type;
                    if (p.group) this.selectedGroup = p.group;
                }
                this.selectProduct(this.preselectedProductId, this.preselectedProductName, this.preselectedProductPrice);
            } else if (this.isPostpaidMode) {
                // Auto-select first available product for postpaid (usually 1 product)
                const available = this.allProducts.filter(p => p.status === 'available');
                if (available.length === 1) {
                    this.selectProduct(available[0].id, available[0].name, available[0].price);
                }
                // Auto-select first type/group
                if (this.hasTypes && this.productTypes.length > 0) {
                    this.selectTypeLevel(this.productTypes[0].name);
                } else if (this.hasGroups && this.productGroups.length > 0) {
                    this.selectGroup(this.productGroups[0].name);
                }
            } else {
                // Auto-select first type or group
                if (this.hasTypes && this.productTypes.length > 0) {
                    this.selectTypeLevel(this.productTypes[0].name);
                } else if (this.hasGroups && this.productGroups.length > 0) {
                    this.selectGroup(this.productGroups[0].name);
                }
            }
        },

        get providerMismatch() {
            if (!this.isPulsaMode || !this.detectedProvider) return false;
            const catName = this.categoryName.toLowerCase();
            const prov = this.detectedProvider.toLowerCase();
            return !catName.includes(prov) && !prov.includes(catName);
        },

        get filteredProducts() {
            let filtered = [];
            
            if (this.isPulsaMode) {
                if (!this.detectedProvider) return [];
                if (this.providerMismatch) return [];
                filtered = this.allProducts;
            } else {
                filtered = this.allProducts;
            }

            // Filter by selectedType if applicable
            if (this.hasTypes && this.selectedType) {
                filtered = filtered.filter(p => p.type === this.selectedType);
            }

            // Filter further by selectedGroup if applicable
            if ((this.hasSubGroups || (!this.hasTypes && this.hasGroups)) && this.selectedGroup) {
                filtered = filtered.filter(p => p.group === this.selectedGroup);
            }

            return filtered;
        },

        get canSubmit() {
            if (this.isPostpaidMode) {
                return this.target && this.selectedProduct && this.selectedPayment && this.whatsapp && !this.isSubmitting && this.inquiryResult && this.inquiryResult.success;
            }
            return this.target && this.selectedProduct && this.selectedPayment && this.whatsapp && !this.isSubmitting;
        },

        get displayPrice() {
            if (this.isPostpaidMode && this.inquiryResult && this.inquiryResult.success) {
                return this.inquiryResult.price;
            }
            return this.selectedProductPrice;
        },

        onTargetInput() {
            if (this.isPostpaidMode) {
                // Reset inquiry when customer number changes
                this.inquiryResult = null;
                this.inquiryRefId = null;
                return;
            }
            if (!this.isPulsaMode) return;
            const provider = detectProvider(this.target);
            this.detectedProvider = provider;
            this.providerColor = provider ? (PROVIDER_COLORS[provider] || 'bg-gray-600') : '';
            this.providerLogo = provider ? (PROVIDER_LOGOS[provider] || '') : '';
            this.selectedProduct = null;
            this.selectedProductName = '';
            this.selectedProductPrice = 0;
        },

        cekTagihan() {
            if (!this.target || this.target.length < 4 || this.isInquiryLoading) return;

            // Auto-select first product if only one available (common for postpaid)
            let productId = this.selectedProduct;
            if (!productId) {
                const available = this.allProducts.filter(p => p.status === 'available');
                if (available.length >= 1) {
                    productId = available[0].id;
                    this.selectProduct(available[0].id, available[0].name, available[0].price);
                }
            }
            if (!productId) {
                showToast('warning', 'Pilih Produk', 'Pilih produk/layanan terlebih dahulu.');
                return;
            }

            this.isInquiryLoading = true;
            this.inquiryResult = null;
            this.inquiryRefId = null;

            fetch('{{ route("api.inquiry") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    product_id: productId,
                    customer_no: String(this.target),
                })
            })
            .then(response => response.json())
            .then(data => {
                this.isInquiryLoading = false;
                this.inquiryResult = data;

                if (data.success) {
                    this.inquiryRefId = data.ref_id;
                    // Override price with inquiry result
                    this.selectedProductPrice = data.price;
                    showToast('success', 'Tagihan Ditemukan', 'Atas nama: ' + data.customer_name);
                } else {
                    showToast('error', 'Tagihan Tidak Ditemukan', data.message || 'Periksa kembali nomor pelanggan.');
                }
            })
            .catch(error => {
                this.isInquiryLoading = false;
                console.error('Inquiry error:', error);
                showToast('error', 'Gagal Cek Tagihan', 'Tidak dapat terhubung ke server.');
            });
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
            this.selectedProductPrice = 0;

            // Auto-select first sub-group if available
            if (this.hasSubGroups && this.currentSubGroups.length > 0) {
                this.selectedGroup = this.currentSubGroups[0].name;
            }
        },

        selectGroup(groupName) {
            this.selectedGroup = groupName;
            this.selectedProduct = null;
            this.selectedProductName = '';
            this.selectedProductPrice = 0;
        },

        selectProduct(id, name, price) {
            this.selectedProduct = id;
            this.selectedProductName = name;
            this.selectedProductPrice = price;
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

        submitCheckout() {
            if (!this.canSubmit) return;
            this.isSubmitting = true;
            showOverlay();

            const payload = {
                target_id: String(this.target),
                target_zone: this.targetZone ? String(this.targetZone) : '',
                product_id: this.selectedProduct,
                payment_method: String(this.selectedPayment),
                quantity: 1,
                customer_whatsapp: String(this.whatsapp),
                customer_email: this.email ? String(this.email) : '',
            };

            // Include inquiry ref for postpaid
            if (this.isPostpaidMode && this.inquiryRefId) {
                payload.inquiry_ref = this.inquiryRefId;
                payload.inquiry_price = this.inquiryResult ? this.inquiryResult.price : 0;
            }

            fetch('{{ route("checkout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify(payload)
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
                    showToast('success', 'Pembayaran Berhasil Diproses!', 'Mengarahkan ke halaman pembayaran...', 3000);
                    setTimeout(() => { window.location.href = data.redirect_url; }, 1200);
                } else {
                    this.isSubmitting = false;
                    showToast('error', 'Transaksi Gagal', data.message || 'Terjadi kesalahan sistem. Silakan coba lagi.');
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
@endsection
