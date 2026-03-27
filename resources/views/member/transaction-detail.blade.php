@extends('layouts.front')

@section('title', 'Detail Transaksi ' . $transaction->invoice_number)

@section('content')
<div class="max-w-[1280px] mx-auto px-4 mt-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        <div class="w-full lg:w-1/4">
            <x-member-sidebar />
        </div>

        <!-- Main Content -->
        <div class="w-full lg:w-3/4">
            
            <div class="mb-4">
                <a href="{{ route('member.transactions') }}" class="text-gray-400 hover:text-white transition text-sm flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Riwayat
                </a>
            </div>

            <!-- Transaction Card -->
            <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl shadow-lg overflow-hidden">
                
                <!-- Header Status -->
                <div class="p-6 border-b border-[#2d3748] flex flex-col md:flex-row justify-between items-start md:items-center gap-4
                    @if($transaction->transaction_status == 'success') bg-emerald-500/5
                    @elseif($transaction->payment_status == 'unpaid') bg-red-500/5
                    @else bg-yellow-500/5 @endif
                ">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Detail Pesanan</p>
                        <h2 class="text-2xl font-black text-white font-mono">{{ $transaction->invoice_number }}</h2>
                        <p class="text-sm text-gray-500 mt-1"><i class="far fa-calendar-alt mr-1"></i> {{ $transaction->created_at->format('d F Y, H:i T') }}</p>
                    </div>
                    <div class="flex flex-col items-end">
                        @if($transaction->payment_status == 'unpaid')
                            <span class="px-4 py-2 bg-red-500 text-white font-black rounded-lg text-sm shadow-[0_0_15px_rgba(239,68,68,0.4)]">MENUNGGU PEMBAYARAN</span>
                            @if($transaction->checkout_url)
                                <a href="{{ $transaction->checkout_url }}" target="_blank" class="mt-3 text-xs font-bold text-red-400 hover:text-red-300 border border-red-500 hover:bg-red-500/10 px-4 py-2 rounded-lg transition text-center w-full">Bayar Sekarang <i class="fas fa-external-link-alt ml-1"></i></a>
                            @endif
                        @elseif($transaction->transaction_status == 'success')
                            <span class="px-4 py-2 bg-emerald-500 text-white font-black rounded-lg text-sm shadow-[0_0_15px_rgba(16,185,129,0.4)]"><i class="fas fa-check-circle mr-1"></i> TRANSAKSI SUKSES</span>
                        @elseif($transaction->transaction_status == 'pending' || $transaction->transaction_status == 'processing')
                            <span class="px-4 py-2 bg-yellow-500 text-white font-black rounded-lg text-sm shadow-[0_0_15px_rgba(245,158,11,0.4)]"><i class="fas fa-sync-alt fa-spin mr-1"></i> DALAM PROSES</span>
                        @else
                            <span class="px-4 py-2 bg-gray-600 text-white font-black rounded-lg text-sm"><i class="fas fa-times-circle mr-1"></i> DIBATALKAN</span>
                        @endif
                    </div>
                </div>

                <!-- Product Detail Section -->
                <div class="p-6">
                    <h3 class="font-bold text-white mb-4 text-lg border-l-4 border-[#f97316] pl-3">Informasi Item</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($transaction->items as $item)
                        <div class="bg-[#252b43] border border-[#2d3748] rounded-xl p-4 flex items-center gap-4">
                            <div class="w-16 h-16 bg-[#1d2235] border border-[#2d3748] rounded-lg flex items-center justify-center shrink-0">
                                <i class="fas fa-gamepad text-2xl text-gray-400"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-white">{{ $item->product_name }}</h4>
                                <p class="text-xs text-[#f97316] font-semibold mb-2">{{ $transaction->game_name ?? '-' }}</p>
                                
                                <!-- Game Data -->
                                @if($item->game_data)
                                    <div class="bg-[#1d2235] p-2 rounded border border-[#2d3748] text-xs font-mono text-gray-300">
                                        @foreach($item->game_data as $key => $value)
                                            <div class="flex justify-between mb-1 last:mb-0">
                                                <span class="text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                                                <span class="font-semibold text-white">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 mb-1">{{ $item->quantity }}x</p>
                                <p class="font-bold text-white">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Payment Details Section -->
                <div class="border-t border-[#2d3748] p-6 bg-[#252b43]">
                    <h3 class="font-bold text-white mb-4 text-lg border-l-4 border-[#f97316] pl-3">Rincian Pembayaran</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Invoice Info -->
                        <div class="space-y-3">
                            <div class="flex justify-between border-b border-[#2d3748] pb-2">
                                <span class="text-gray-400 text-sm">Metode Pembayaran</span>
                                <span class="text-white font-semibold">{{ $transaction->payment_method }}</span>
                            </div>
                            <div class="flex justify-between border-b border-[#2d3748] pb-2">
                                <span class="text-gray-400 text-sm">Waktu Dibuat</span>
                                <span class="text-white font-semibold">{{ $transaction->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="flex justify-between border-b border-[#2d3748] pb-2">
                                <span class="text-gray-400 text-sm">Waktu Lunas</span>
                                <span class="text-white font-semibold">{{ $transaction->paid_at ? $transaction->paid_at->format('d M Y, H:i') : '-' }}</span>
                            </div>
                        </div>

                        <!-- Price Info -->
                        <div class="bg-[#1d2235] rounded-xl p-4 border border-[#2d3748]">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-400 text-sm">Subtotal</span>
                                <span class="text-white font-semibold">Rp {{ number_format($transaction->items->sum(function($i) { return $i->price * $i->quantity; }), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between mb-4 border-b border-[#2d3748]/50 pb-4">
                                <span class="text-gray-400 text-sm">Biaya Admin (Payment Gateway)</span>
                                <span class="text-white font-semibold">Rp {{ number_format($transaction->admin_fee, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white font-bold">Total Pembayaran</span>
                                <span class="text-[#f97316] font-black text-xl">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Support Notice -->
                <div class="bg-[#1d2235] p-4 text-center border-t border-[#2d3748]">
                    <p class="text-xs text-gray-500">Ada kendala dengan pesanan Anda? Hubungi <a href="{{ route('front.page', 'kontak') }}" class="text-[#f97316] hover:underline">Customer Support</a> kami dengan menyertakan Invoice Number.</p>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
