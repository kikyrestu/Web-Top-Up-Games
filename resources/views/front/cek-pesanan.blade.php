@extends('layouts.front')
@section('title', 'Cek Status Pesanan')
@section('meta_description', 'Lacak status pesanan berdasarkan nomor invoice atau data tujuan.')
@section('canonical', route('front.cek-pesanan'))
@section('robots', 'noindex,nofollow,noarchive')

@section('content')

<section class="relative pt-32 pb-20 overflow-hidden min-h-screen bg-[#121212]">
    <div class="absolute inset-0 bg-gradient-to-b from-[#1c1c1c] via-[#121212] to-[#121212] z-0"></div>
    <div class="absolute top-0 right-0 w-64 h-64 bg-[#f97316]/10 rounded-full blur-[100px]"></div>
    <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#f97316]/5 rounded-full blur-[80px]"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 max-w-4xl">
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-4">
                Cek <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#f97316] to-[#ff983f]">Pesanan</span>
            </h1>
            <p class="text-gray-400 max-w-2xl mx-auto text-lg">
                Lacak status transaksi kamu dengan mudah menggunakan Nomor Invoice atau ID Tujuan/No HP.
            </p>
        </div>

        <div class="bg-[#1c1c1c] p-6 md:p-10 rounded-3xl border border-gray-800 shadow-[0_20px_50px_rgba(0,0,0,0.5)] mb-12 relative overflow-hidden">
            <div class="absolute -top-10 -right-10 text-[150px] text-[#f97316]/5 pointer-events-none">
                <i class="fas fa-search"></i>
            </div>

            <form action="{{ route('front.proses-cek-pesanan') }}" method="POST" class="relative z-10">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1">
                        <label class="block mb-2 text-sm font-semibold text-gray-300">Cari Berdasarkan</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-[#f97316]">
                                <i class="fas fa-filter"></i>
                            </div>
                            <select name="search_type" class="bg-[#121212] border border-gray-700 text-white text-sm rounded-xl focus:ring-[#f97316] focus:border-[#f97316] block w-full pl-10 p-4 transition-colors">
                                <option value="invoice">Nomor Invoice</option>
                                <option value="target">Tujuan / No HP Customer</option>
                            </select>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-2 text-sm font-semibold text-gray-300">Data Pencarian</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-gray-500">
                                <i class="fas fa-barcode"></i>
                            </div>
                            <input type="text" name="search_value" value="{{ request('search_value') }}" required class="bg-[#121212] border border-gray-700 text-white text-sm rounded-xl focus:ring-[#f97316] focus:border-[#f97316] block w-full pl-10 p-4 transition-colors" placeholder="Cth: INV-XXXXXXXXXX atau 08123456789">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="bg-gradient-to-r from-[#f97316] to-[#ea580c] hover:from-[#ea580c] hover:to-[#c2410c] text-white font-bold py-3.5 px-8 rounded-xl transition-all duration-300 shadow-[0_0_20px_rgba(249,115,22,0.3)] hover:shadow-[0_0_25px_rgba(249,115,22,0.5)] transform hover:-translate-y-0.5 flex items-center w-full md:w-auto justify-center">
                        <i class="fas fa-search mr-2"></i> Lacak Sekarang
                    </button>
                </div>
            </form>
        </div>

        @php $showResults = isset($transactions); @endphp

        @if($showResults && $transactions->count() > 0)
            <h3 class="text-2xl font-bold text-white mb-6 flex items-center">
                <i class="fas fa-list-alt text-[#f97316] mr-3"></i> Hasil Pencarian
            </h3>

            <div class="space-y-6">
                @foreach($transactions as $trx)
                <div class="bg-[#1c1c1c] border border-gray-800 rounded-2xl overflow-hidden hover:border-gray-700 transition-colors">
                    <div class="p-4 md:p-6 flex flex-col md:flex-row md:items-center justify-between border-b border-gray-800 bg-gray-900/30">
                        <div>
                            <div class="text-[#f97316] font-bold text-lg mb-1">{{ $trx->invoice_number }}</div>
                            <div class="text-gray-400 text-sm flex items-center">
                                <i class="far fa-clock mr-2"></i> {{ $trx->created_at->translatedFormat('d M Y H:i') }}
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex gap-3">
                            @php
                                $payBadge = match($trx->payment_status) {
                                    'paid' => ['bg-green-500/10 text-green-500 border-green-500/20', 'fa-check-circle', 'PAID'],
                                    'failed' => ['bg-red-500/10 text-red-500 border-red-500/20', 'fa-times-circle', 'FAILED'],
                                    default => ['bg-yellow-500/10 text-yellow-500 border-yellow-500/20', 'fa-history', 'UNPAID'],
                                };
                                $trxBadge = match($trx->transaction_status) {
                                    'success' => ['bg-blue-500/10 text-blue-500 border-blue-500/20', 'fa-gamepad', 'SUCCESS'],
                                    'processing' => ['bg-orange-500/10 text-orange-500 border-orange-500/20', 'fa-spinner fa-spin', 'PROCESS'],
                                    default => ['bg-gray-500/10 text-gray-400 border-gray-500/20', 'fa-hourglass-start', strtoupper($trx->transaction_status)],
                                };
                            @endphp
                            <span class="px-4 py-1.5 rounded-full {{ $payBadge[0] }} text-xs font-bold border flex items-center"><i class="fas {{ $payBadge[1] }} mr-1.5"></i> {{ $payBadge[2] }}</span>
                            <span class="px-4 py-1.5 rounded-full {{ $trxBadge[0] }} text-xs font-bold border flex items-center"><i class="fas {{ $trxBadge[1] }} mr-1.5"></i> {{ $trxBadge[2] }}</span>
                        </div>
                    </div>

                    <div class="p-4 md:p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold mb-1 uppercase tracking-wider">Detail Produk</p>
                            @foreach($trx->items as $item)
                                <p class="text-white font-medium">{{ $item->product_name }}</p>
                            @endforeach
                            <p class="text-xs text-gray-500 font-semibold mt-4 mb-1 uppercase tracking-wider">Tujuan / Target</p>
                            <p class="text-gray-300">{{ $trx->target_input }}</p>
                        </div>
                        <div class="md:text-right">
                            <p class="text-xs text-gray-500 font-semibold mb-1 uppercase tracking-wider">Metode Pembayaran</p>
                            <p class="text-gray-300">{{ $trx->paymentGateway->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500 font-semibold mt-4 mb-1 uppercase tracking-wider">Total Pembayaran</p>
                            <p class="text-2xl font-bold text-[#f97316]">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="p-4 border-t border-gray-800 bg-black/20 flex justify-end">
                        <a href="{{ route('front.invoice', $trx->invoice_number) }}" class="text-sm text-gray-400 hover:text-white transition-colors flex items-center">
                            Lihat Invoice Detail <i class="fas fa-arrow-right ml-2 text-[#f97316]"></i>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @elseif($showResults && $transactions->count() === 0)
            <div class="text-center py-12 bg-[#1c1c1c] rounded-3xl border border-gray-800">
                <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-500/20">
                    <i class="fas fa-search-minus text-3xl text-red-500"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Yeiks, Pesanan Tidak Ditemukan!</h3>
                <p class="text-gray-400 text-sm">Pastikan Nomor Invoice atau Nomor Tujuan yang kamu masukkan sudah benar.</p>
            </div>
        @endif

    </div>
</section>

@endsection
