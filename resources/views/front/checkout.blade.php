@extends('layouts.front')
@section('title', 'Checkout Pesanan')
@section('meta_description', 'Konfirmasi dan selesaikan pembayaran pesanan top up Anda.')
@section('canonical', route('front.checkout'))
@section('robots', 'noindex,nofollow,noarchive')
@push('jsonld')
<script type="application/ld+json">
{
    "{{ '@' }}context": "https://schema.org",
    "{{ '@' }}type": "WebPage",
    "name": "Checkout Pesanan",
    "url": "{{ route('front.checkout') }}",
    "description": "Konfirmasi dan selesaikan pembayaran pesanan top up Anda."
}
</script>
@endpush

@section('content')

<main class="flex-grow pt-24 pb-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <h1 class="text-2xl font-bold text-white mb-6">Konfirmasi Pesanan</h1>

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500 text-red-500 rounded-xl p-4 mb-6">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('checkout') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @csrf
            
            <!-- Hidden inputs from previous step -->
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="target_id" value="{{ $target }}">
            <!-- PPOB usually has no zone, but let's pass empty -->
            <input type="hidden" name="target_zone" value="">
            <input type="hidden" name="quantity" value="1">

            <div class="lg:col-span-2 space-y-6">
                <!-- Data Tujuan -->
                <div class="bg-up-card border border-up-border rounded-xl p-5">
                    <h2 class="text-lg font-bold text-white mb-4">1. Data Tujuan</h2>
                    <div class="bg-[#11131c] rounded-lg p-4 border border-up-border/50">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-400">Produk</p>
                                <p class="text-base text-white font-medium">{{ $product->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Tujuan (Nomor HP / ID)</p>
                                <p class="text-base text-up-yellow font-bold">{{ $target }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Kontak -->
                <div class="bg-up-card border border-up-border rounded-xl p-5">
                    <h2 class="text-lg font-bold text-white mb-4">2. Informasi Kontak</h2>
                    <p class="text-sm text-gray-400 mb-4">Bukti pembayaran akan dikirimkan ke kontak di bawah ini.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-300 font-semibold mb-1.5">Nomor WhatsApp *</label>
                            <input type="text" name="customer_whatsapp" required placeholder="0812xxxxxx" class="w-full bg-[#111620] border border-up-border rounded-lg px-3 py-2.5 text-sm text-white focus:border-up-yellow focus:ring-1 focus:ring-up-yellow outline-none transition">
                            @error('customer_whatsapp') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm text-gray-300 font-semibold mb-1.5">Email (Opsional)</label>
                            <input type="email" name="customer_email" placeholder="email@domain.com" class="w-full bg-[#111620] border border-up-border rounded-lg px-3 py-2.5 text-sm text-white focus:border-up-yellow focus:ring-1 focus:ring-up-yellow outline-none transition">
                            @error('customer_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="bg-up-card border border-up-border rounded-xl p-5">
                    <h2 class="text-lg font-bold text-white mb-4">3. Pilih Pembayaran</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3" x-data="{ selectedPayment: '' }">
                        @foreach($paymentGateways as $pg)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment_gateway_id" value="{{ $pg->id }}" class="peer sr-only" x-model="selectedPayment">
                            <div class="p-3 border border-up-border rounded-lg bg-[#11131c] hover:border-up-yellow transition peer-checked:border-up-yellow peer-checked:bg-up-yellow/10 flex items-center justify-between">
                                <span class="text-sm font-semibold text-white">{{ $pg->name }}</span>
                                <div class="text-right">
                                    <span class="text-xs text-up-yellow block">Rp {{ number_format($product->price_sell + $pg->fee_flat, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @error('payment_gateway_id') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Ringkasan -->
            <div class="lg:col-span-1">
                <div class="bg-up-card border border-up-border rounded-xl p-5 sticky top-24">
                    <h2 class="text-lg font-bold text-white mb-4 border-b border-up-border pb-3">Ringkasan Pembayaran</h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Harga Produk</span>
                            <span class="text-white font-medium">Rp {{ number_format($product->price_sell, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400">Biaya Admin (Estimasi)</span>
                            <span class="text-white font-medium">Auto calculate di akhir</span>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-up-yellow text-black hover:bg-yellow-600 py-3 rounded-lg text-sm font-bold transition shadow-sm">
                        Checkout Sekarang
                    </button>
                    <p class="text-xs text-center text-gray-500 mt-3">Pastikan data tujuan sudah benar</p>
                </div>
            </div>

        </form>
    </div>
</main>
@endsection