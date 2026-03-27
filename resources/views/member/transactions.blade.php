@extends('layouts.front')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 mt-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        <div class="w-full lg:w-1/4">
            <x-member-sidebar />
        </div>

        <!-- Main Content -->
        <div class="w-full lg:w-3/4">
            <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl shadow-lg overflow-hidden flex flex-col min-h-[600px]">
                
                <!-- Header & Filters -->
                <div class="p-6 border-b border-[#2d3748] bg-[#252b43]">
                    <h2 class="text-xl font-bold text-white mb-4"><i class="fas fa-history text-[#f97316] mr-2"></i> Riwayat Transaksi</h2>
                    
                    <form action="{{ route('member.transactions') }}" method="GET" class="flex flex-wrap gap-2">
                        <a href="{{ route('member.transactions') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ !request('status') ? 'bg-[#f97316] text-white shadow-lg shadow-[#f97316]/20' : 'bg-[#1d2235] text-gray-400 border border-[#2d3748] hover:text-white hover:border-[#f97316]' }}">
                            Semua
                        </a>
                        <a href="{{ route('member.transactions', ['status' => 'success']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') == 'success' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'bg-[#1d2235] text-gray-400 border border-[#2d3748] hover:text-white hover:border-emerald-500 border' }}">
                            Sukses
                        </a>
                        <a href="{{ route('member.transactions', ['status' => 'pending']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') == 'pending' ? 'bg-yellow-500 text-white shadow-lg shadow-yellow-500/20' : 'bg-[#1d2235] text-gray-400 border border-[#2d3748] hover:text-white hover:border-yellow-500' }}">
                            Proses
                        </a>
                        <a href="{{ route('member.transactions', ['status' => 'unpaid']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') == 'unpaid' ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'bg-[#1d2235] text-gray-400 border border-[#2d3748] hover:text-white hover:border-red-500' }}">
                            Belum Bayar
                        </a>
                        <a href="{{ route('member.transactions', ['status' => 'failed']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ request('status') == 'failed' ? 'bg-gray-600 text-white shadow-lg shadow-gray-600/20' : 'bg-[#1d2235] text-gray-400 border border-[#2d3748] hover:text-white hover:border-gray-500' }}">
                            Batal
                        </a>
                    </form>
                </div>

                <!-- Transaction List -->
                <div class="flex-grow flex flex-col">
                    @forelse($transactions as $trx)
                        <div class="p-6 border-b border-[#2d3748] hover:bg-[#252b43] transition-colors last:border-0 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                            
                            <!-- Left: Info -->
                            <div class="flex items-start gap-4 flex-1">
                                <div class="w-12 h-12 bg-[#1d2235] rounded-xl flex items-center justify-center shrink-0 border border-[#2d3748]">
                                    <i class="fas fa-gamepad text-xl text-gray-400"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-bold text-white text-base">{{ $trx->game_name ?? 'Pesanan Transaksi' }}</h3>
                                        @if($trx->payment_status == 'unpaid')
                                            <span class="px-2 py-0.5 bg-red-500 text-white text-[10px] font-bold rounded">BELUM BAYAR</span>
                                        @elseif($trx->transaction_status == 'success')
                                            <span class="px-2 py-0.5 bg-emerald-500 text-white text-[10px] font-bold rounded">SUKSES</span>
                                        @elseif($trx->transaction_status == 'pending' || $trx->transaction_status == 'processing')
                                            <span class="px-2 py-0.5 bg-yellow-500 text-white text-[10px] font-bold rounded">PROSES</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-gray-600 text-white text-[10px] font-bold rounded">GAGAL</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-400 font-mono">{{ $trx->invoice_number }}</p>
                                    <p class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                        <i class="far fa-clock"></i> {{ $trx->created_at->format('d M Y, H:i') }}
                                        <span class="inline-block w-1 h-1 rounded-full bg-gray-600 mx-1"></span>
                                        <i class="far fa-credit-card"></i> {{ $trx->payment_method }}
                                    </p>
                                </div>
                            </div>

                            <!-- Right: Harga & Action -->
                            <div class="text-left md:text-right w-full md:w-auto mt-2 md:mt-0 flex flex-row md:flex-col justify-between md:justify-end items-center md:items-end">
                                <div>
                                    <p class="text-xs text-gray-400 mb-0.5 hidden md:block">Total Harga</p>
                                    <p class="font-black text-[#f97316] text-lg">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</p>
                                </div>
                                <a href="{{ route('member.transactions.show', $trx->invoice_number) }}" class="mt-0 md:mt-2 px-4 py-2 bg-[#2d3748] hover:bg-[#3f4a61] text-white text-xs font-bold rounded-lg transition-colors border border-[#3f4a61]">
                                    Detail <i class="fas fa-chevron-right ml-1 text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="flex-grow flex flex-col justify-center items-center p-12 text-center h-full">
                            <i class="fas fa-box-open text-6xl text-gray-600 mb-4"></i>
                            <h3 class="text-xl font-bold text-white mb-2">Belum ada transaksi</h3>
                            <p class="text-gray-400 text-sm mb-6 max-w-sm">Mulai belanja dan temukan berbagai macam top up game dan voucher murah di platform kami.</p>
                            <a href="{{ route('front.index') }}" class="px-6 py-3 bg-gradient-to-r from-[#f97316] to-[#ea580c] hover:from-[#ea580c] hover:to-[#c2410c] text-white font-bold rounded-xl shadow-[0_0_20px_rgba(249,115,22,0.3)] transition-all">
                                Berbelanja Sekarang
                            </a>
                        </div>
                    @endforelse
                </div>
                
                <!-- Pagination -->
                @if($transactions->hasPages())
                    <div class="p-6 border-t border-[#2d3748] bg-[#1d2235]">
                        {{ $transactions->links('vendor.pagination.tailwind-dark') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
