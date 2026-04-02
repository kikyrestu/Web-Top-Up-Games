@extends('layouts.front')
@section('title', 'Promo & Voucher')
@section('meta_description', 'Dapatkan diskon terbaik dengan voucher eksklusif dari kami.')

@section('content')
<section class="relative pt-28 pb-20 min-h-screen bg-[#121212]">
    <div class="absolute top-0 right-0 w-96 h-96 bg-[#f97316]/10 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-purple-500/8 rounded-full blur-[80px] pointer-events-none"></div>

    <div class="container mx-auto px-4 max-w-5xl relative z-10">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 bg-[#f97316]/10 border border-[#f97316]/20 text-[#f97316] text-xs font-bold px-4 py-2 rounded-full mb-4">
                <i class="fas fa-tag"></i> Promo Aktif
            </div>
            <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">
                🎁 Promo & <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#f97316] to-[#ff983f]">Voucher</span>
            </h1>
            <p class="text-gray-400 max-w-xl mx-auto">Gunakan kode voucher berikut saat checkout untuk mendapat potongan harga eksklusif!</p>
        </div>

        @if($vouchers->isEmpty())
        <div class="text-center py-20 bg-[#1c1c1c] rounded-3xl border border-[#2d2d2d]">
            <i class="fas fa-ticket-alt text-5xl text-gray-600 mb-4 block"></i>
            <p class="text-gray-400 text-lg font-bold">Belum ada promo aktif</p>
            <p class="text-gray-600 text-sm mt-2">Pantau terus halaman ini untuk penawaran terbaik!</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($vouchers as $voucher)
            <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-2xl overflow-hidden hover:border-[#f97316]/30 transition-all duration-300 hover:-translate-y-1 group">
                <!-- Coupon strip top -->
                <div class="h-1.5 bg-gradient-to-r from-[#f97316] via-[#ff983f] to-[#f97316]"></div>

                <div class="flex">
                    <!-- Left: code block -->
                    <div class="bg-[#f97316]/10 border-r border-dashed border-[#f97316]/30 px-6 py-6 flex flex-col items-center justify-center min-w-[130px]">
                        <p class="text-[10px] text-gray-500 uppercase font-bold mb-2 tracking-wider">Kode</p>
                        <p id="code-{{ $voucher->id }}" class="font-black text-[#f97316] text-xl tracking-widest uppercase">{{ $voucher->code }}</p>
                        <button onclick="copyCode('{{ $voucher->code }}', '{{ $voucher->id }}')"
                                class="mt-3 px-3 py-1.5 bg-[#f97316]/20 text-[#f97316] text-[10px] font-bold rounded-lg hover:bg-[#f97316]/30 transition flex items-center gap-1">
                            <i class="fas fa-copy"></i> Salin
                        </button>
                    </div>

                    <!-- Right: details -->
                    <div class="p-5 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-black text-white text-lg">
                                    @if($voucher->type === 'percentage')
                                        Diskon {{ (int)$voucher->value }}%
                                        @if($voucher->max_discount)
                                            <span class="text-xs text-gray-400 font-normal">(max Rp {{ number_format($voucher->max_discount, 0, ',', '.') }})</span>
                                        @endif
                                    @else
                                        Diskon Rp {{ number_format($voucher->value, 0, ',', '.') }}
                                    @endif
                                </p>
                                @if($voucher->description)
                                <p class="text-gray-400 text-xs mt-0.5">{{ $voucher->description }}</p>
                                @endif
                            </div>
                            <span class="px-2 py-0.5 bg-green-500/10 text-green-400 text-[10px] font-bold rounded-full border border-green-500/20 whitespace-nowrap">✅ Aktif</span>
                        </div>

                        <div class="mt-4 space-y-1.5 text-xs text-gray-500">
                            @if($voucher->min_purchase > 0)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-shopping-cart w-3 text-center text-gray-600"></i>
                                Min. pembelian: <span class="text-gray-300 font-bold">Rp {{ number_format($voucher->min_purchase, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            @if($voucher->expires_at)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-clock w-3 text-center text-gray-600"></i>
                                Berlaku sampai: <span class="text-amber-400 font-bold">{{ $voucher->expires_at->format('d M Y') }}</span>
                            </div>
                            @endif
                            @if($voucher->max_uses)
                            <div class="flex items-center gap-2">
                                <i class="fas fa-users w-3 text-center text-gray-600"></i>
                                Sisa: <span class="text-gray-300 font-bold">{{ $voucher->max_uses - $voucher->uses_count }} penggunaan</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- CTA Footer -->
                <div class="border-t border-[#2d2d2d] px-5 py-3 bg-[#151515] flex justify-between items-center">
                    <p class="text-[10px] text-gray-600">Masukkan kode saat checkout</p>
                    <a href="{{ route('home') }}" class="text-[#f97316] text-xs font-bold hover:underline">Belanja Sekarang →</a>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</section>

@push('scripts')
<script>
function copyCode(code, id) {
    navigator.clipboard.writeText(code).then(() => {
        const btn = event.target.closest('button');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Disalin!';
        btn.classList.add('bg-green-500/20', '!text-green-400');
        setTimeout(() => {
            btn.innerHTML = original;
            btn.classList.remove('bg-green-500/20', '!text-green-400');
        }, 2000);
    });
}
</script>
@endpush
@endsection
