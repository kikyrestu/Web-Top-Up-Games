@extends('layouts.front')
@section('title', $category->name . ' Top Up Murah')
@section('meta_description', 'Top up ' . $category->name . ' cepat dan aman. Pilih nominal, metode pembayaran lengkap, dan proses otomatis.')
@section('canonical', route('front.category', $category->slug ?? $category->id))
@if(!empty($category->image))
    @section('meta_image', asset('storage/' . $category->image))
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
<div class="container mx-auto px-4 pt-24 md:pt-28 relative z-20">
    <nav class="text-sm text-gray-400 mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-2">
            <li>
                <a href="{{ route('front.index') }}" class="hover:text-white transition">Beranda</a>
            </li>
            <li>
                <i class="fas fa-chevron-right text-xs text-gray-500"></i>
            </li>
            <li class="text-[#f97316] font-medium">{{ $category->name }}</li>
        </ol>
    </nav>
</div>

<!-- Hero Background & Category Info -->
<div class="relative w-full h-[280px] md:h-[400px]">
    <!-- Background Image -->
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $category->image ? asset('storage/'.$category->image) : 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&q=80' }}'); filter: brightness(0.35);"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#121212] via-transparent to-transparent"></div>
    
    <!-- Game Detail Overlay -->
    <div class="absolute bottom-0 left-0 w-full px-4 md:px-8 lg:px-20 pb-4 flex items-end container mx-auto gap-4 md:gap-6 z-10 translate-y-12 md:translate-y-16">
        <img src="{{ $category->image ? asset('storage/'.$category->image) : 'https://placehold.co/150' }}" alt="{{ $category->name }}" class="w-24 h-24 md:w-40 md:h-40 rounded-xl object-cover border-2 md:border-4 border-[#1c1c1c] shadow-2xl z-20 shrink-0">
        <div class="pb-1 md:pb-2 flex-grow">
            <h1 class="text-2xl md:text-5xl font-black text-white uppercase italic tracking-wider mb-0.5 md:mb-1 drop-shadow-lg">{{ $category->name }}</h1>
            <p class="text-gray-400 text-xs md:text-base mb-2 md:mb-4 font-medium">{{ $category->publisher ?? 'Publisher Unknown' }}</p>
            <div class="flex flex-wrap gap-1 md:gap-2 text-[10px] md:text-xs font-semibold text-gray-300">
                <span class="bg-[#1c1c1c]/90 px-2 py-1 md:px-3 md:py-1.5 rounded text-[#f59e0b] border border-[#2d2d2d] flex items-center gap-1.5"><i class="fas fa-bolt"></i> Proses Cepat</span>
                <span class="bg-[#1c1c1c]/90 px-2 py-1 md:px-3 md:py-1.5 rounded text-blue-400 border border-[#2d2d2d] flex items-center gap-1.5"><i class="fas fa-headset"></i> Layanan Chat 24/7</span>
                <span class="bg-[#1c1c1c]/90 px-2 py-1 md:px-3 md:py-1.5 rounded text-green-400 border border-[#2d2d2d] flex items-center gap-1.5 hidden sm:flex"><i class="fas fa-shield-alt"></i> Pembayaran Aman</span>
            </div>
        </div>
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
                                   class="w-full bg-[#121212] border border-[#333] px-4 py-3 rounded-lg focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition text-white placeholder-gray-500 text-sm">
                        </div>
                        @endforeach
                    </div>
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
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
                            @foreach($products as $product)
                            <div class="border border-[#333] hover:border-[#f97316] bg-[#222] rounded-xl p-3 md:p-4 cursor-pointer relative overflow-hidden group transition-all"
                                 :class="{'border-[#f97316] bg-[#f97316]/5': selectedProduct === {{ $product->id }}}"
                                 @click="selectProduct({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price_sell }})">
                                
                                <div class="flex items-start gap-2 mb-3">
                                    <i class="fas fa-gem text-blue-400 mt-1"></i>
                                    <div class="text-xs md:text-sm font-bold text-gray-200 group-hover:text-white leading-tight">{{ $product->name }}</div>
                                </div>
                                <div class="text-[#f97316] font-black text-sm md:text-base">
                                    Rp {{ number_format($product->price_sell, 0, ',', '.') }}
                                </div>

                                <!-- Checkmark Overlay -->
                                <div class="absolute bottom-3 right-3 text-[#f97316] scale-0 transition-transform duration-200" 
                                     :class="{'scale-100': selectedProduct === {{ $product->id }}}">
                                    <i class="fas fa-check-circle text-lg bg-black rounded-full"></i>
                                </div>
                            </div>
                            @endforeach
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
                        <input type="number" x-model="quantity" readonly class="w-16 bg-transparent text-center text-white font-bold text-lg focus:outline-none pointer-events-none appearance-none">
                        <button @click="quantity++" class="w-10 h-10 flex items-center justify-center text-[#f97316] hover:bg-[#222] rounded-md transition font-bold" type="button"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>

            <!-- 4. Pilih Pembayaran -->
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] overflow-hidden">
                <div class="bg-[#151515] px-4 py-3 md:p-4 border-b border-[#2d2d2d] flex items-center gap-3">
                    <span class="bg-[#f97316] text-white w-7 h-7 md:w-8 md:h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">4</span>
                    <h2 class="text-base md:text-lg font-bold text-white">Pilih Pembayaran</h2>
                </div>
                <div class="p-4 md:p-6" x-data="{ openEwallet: true, openVA: false }">
                    @if($paymentGateways->isEmpty())
                        <div class="text-center text-gray-500 py-8 bg-[#121212] rounded-lg border border-dashed border-[#333]">
                            <p class="text-sm text-gray-400">Metode pembayaran belum dikonfigurasi.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            <!-- Direct Items (e.g. QRIS, Credit) -->
                            @foreach($paymentGateways->take(2) as $pg)
                            <div class="border border-[#333] hover:border-[#f97316] bg-[#222] rounded-xl p-3 md:p-4 cursor-pointer relative overflow-hidden group transition-all"
                                 :class="{'border-[#f97316] bg-[#f97316]/5': selectedPayment === {{ $pg->id }}}"
                                 @click="selectPayment({{ $pg->id }}, '{{ $pg->name }}')">
                                 
                                <!-- Orange Tag top right -->
                                <div class="absolute top-0 right-0 bg-[#f97316] text-white text-[9px] font-bold px-3 py-1 rounded-bl-lg transform origin-top-right z-10">
                                    CEPAT & MUDAH
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-2">
                                    <div class="flex items-center gap-4">
                                        @if($pg->logo)
                                            <div class="w-16 h-10 md:w-20 md:h-12 bg-white rounded-lg flex items-center justify-center p-1.5 shrink-0">
                                                <img src="{{ asset('storage/'.$pg->logo) }}" class="max-h-full max-w-full object-contain">
                                            </div>
                                        @else
                                            <div class="w-16 h-10 md:w-20 md:h-12 bg-[#333] rounded-lg flex items-center justify-center text-gray-400 shrink-0">
                                                <i class="fas fa-qrcode text-xl"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h3 class="font-bold text-sm md:text-base text-gray-200 group-hover:text-white transition">{{ $pg->name }}</h3>
                                            <p class="text-[10px] md:text-xs text-gray-500 line-through mt-0.5">Rp <span x-text="formatRupiah((selectedPrice * quantity) + 1250)"></span></p>
                                        </div>
                                    </div>
                                    <div class="text-[#f97316] font-black text-sm md:text-lg">
                                        Rp <span x-text="formatRupiah(selectedPrice * quantity)"></span>
                                    </div>
                                </div>
                                <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#f97316] scale-y-0 transition-transform duration-200" :class="{'scale-y-100': selectedPayment === {{ $pg->id }}}"></div>
                            </div>
                            @endforeach

                            <!-- Accordion: E-Wallet -->
                            <div class="border border-[#333] bg-[#1c1c1c] rounded-xl overflow-hidden mt-4">
                                <button @click="openEwallet = !openEwallet" class="w-full bg-[#151515] px-4 py-3 md:p-4 flex items-center justify-between hover:bg-[#222] transition outline-none">
                                    <h3 class="text-gray-300 font-bold text-sm md:text-base">E-Wallet</h3>
                                    <i class="fas fa-chevron-down text-gray-500 transition-transform duration-300" :class="{'rotate-180': openEwallet}"></i>
                                </button>
                                <div x-show="openEwallet" x-collapse>
                                    <div class="p-3 md:p-4 grid grid-cols-1 gap-3 bg-[#222]">
                                        @foreach($paymentGateways->skip(2) as $pg)
                                        <div class="border border-[#444] hover:border-[#f97316] bg-[#1c1c1c] rounded-xl p-3 flex flex-col md:flex-row md:items-center justify-between gap-3 cursor-pointer group"
                                             :class="{'border-[#f97316]': selectedPayment === {{ $pg->id }}}"
                                             @click="selectPayment({{ $pg->id }}, '{{ $pg->name }}')">
                                            <div class="flex items-center gap-3">
                                                 @if($pg->logo)
                                                    <div class="w-14 h-8 md:w-16 md:h-10 bg-white rounded flex items-center justify-center p-1 shrink-0">
                                                        <img src="{{ asset('storage/'.$pg->logo) }}" class="max-h-full max-w-full object-contain">
                                                    </div>
                                                @else
                                                    <div class="w-14 h-8 md:w-16 md:h-10 bg-[#333] rounded flex items-center justify-center text-gray-400 shrink-0">
                                                        <i class="fas fa-wallet"></i>
                                                    </div>
                                                @endif
                                                <div class="text-sm font-bold text-gray-300 group-hover:text-white">{{ $pg->name }}</div>
                                            </div>
                                            <div class="text-[#f97316] font-bold text-sm">
                                                Rp <span x-text="formatRupiah(selectedPrice * quantity)"></span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
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
                    <p>Cara top up {{ $category->name }} proses cepat instan dengan pembayaran 100% aman terlengkap:</p>
                    <ol class="list-decimal pl-5 space-y-1 mt-2 mb-4">
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
            <div class="bg-[#1c1c1c] rounded-xl border border-[#2d2d2d] p-4 flex items-center gap-4 group cursor-pointer hover:bg-[#222] transition">
                <div class="w-10 h-10 rounded-full bg-[#f97316]/10 flex items-center justify-center text-[#f97316]">
                    <i class="fas fa-headset text-xl group-hover:scale-110 transition-transform"></i>
                </div>
                <div>
                    <h4 class="text-white font-bold text-sm">Butuh Bantuan?</h4>
                    <p class="text-xs text-gray-400">Kamu bisa hubungi admin disini.</p>
                </div>
            </div>

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
                            <img src="{{ $category->image ? asset('storage/'.$category->image) : 'https://placehold.co/150' }}" class="w-12 h-12 rounded object-cover border border-[#444]">
                            <div>
                                <p class="text-white text-sm font-bold" x-text="selectedProductName">Product Name</p>
                                <p class="text-xs text-gray-400" x-text="'Jumlah: ' + quantity + 'x'">Jumlah: 1x</p>
                            </div>
                        </div>

                        <div class="space-y-2 text-sm mt-4">
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
                            :disabled="!target || !wa"
                            :class="{'opacity-70 cursor-not-allowed': !target || !wa}">
                            <i class="fas fa-shopping-bag mr-2"></i> Pesan Sekarang!
                        </button>
                        
                        <!-- Validation warning inside checkout -->
                        <p x-show="!target || !wa" class="text-[10px] text-red-400 mt-2 text-center" x-transition>Harap isi Data Akun dan Whatsapp</p>
                    </div>
                </div>

            </div>
            
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('checkoutPage', () => ({
            target: '',
            targetZone: '',
            email: '',
            wa: '',
            promo: '',
            
            quantity: 1,
            selectedProduct: null,
            selectedProductName: '',
            selectedPrice: 0,
            
            selectedPayment: null,
            selectedPaymentName: '',

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

            formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            },

            submitCheckout() {
                if(!this.target) {
                    alert('Mohon isi ID / Nomor Tujuan (Data Akun)');
                    return;
                }
                if(!this.wa) {
                    alert('Mohon isi Nomor WhatsApp pada Detail Kontak');
                    return;
                }
                
                // Submit to backend via fetch API
                fetch('{{ route('checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        customer_email: this.email,
                        customer_whatsapp: this.wa,
                        target_id: this.target,
                        target_zone: this.targetZone,
                        product_id: this.selectedProduct,
                        payment_gateway_id: this.selectedPayment,
                        quantity: this.quantity
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        window.location.href = data.redirect_url;
                    } else {
                        alert(data.message || 'Terjadi kesalahan sistem');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal terhubung ke server');
                });
            }
        }));
    });
</script>
@endpush
@endsection