@extends('layouts.front')

@section('title', 'Dashboard Member')

@section('content')
<div class="max-w-[1280px] mx-auto px-4 mt-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        <div class="w-full lg:w-1/4">
            <x-member-sidebar />
        </div>

        <!-- Main Content -->
        <div class="w-full lg:w-3/4 space-y-6">
            
            <!-- Welcome Header -->
            <div class="bg-gradient-to-r from-[#1d2235] to-[#f97316]/10 border border-[#f97316]/20 rounded-2xl p-6 shadow-lg">
                <h2 class="text-2xl font-black text-white mb-2">Selamat datang, {{ auth()->user()->name }}! 🚀</h2>
                <p class="text-gray-400 text-sm">Kelola transaksi, top up wallet, dan intip promo terbaru di sini.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Saldo -->
                <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl p-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-wallet text-6xl text-[#f97316]"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-400 mb-1">Saldo Wallet</p>
                    <h3 class="text-3xl font-black text-white mb-4">Rp {{ number_format($user->wallet_balance, 0, ',', '.') }}</h3>
                    <a href="{{ route('member.wallet') }}" class="inline-flex items-center text-xs font-bold text-[#f97316] hover:text-[#ea580c] transition">
                        Top Up Saldo <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <!-- Total Transaksi -->
                <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl p-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-shopping-cart text-6xl text-blue-500"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-400 mb-1">Total Pesanan</p>
                    <h3 class="text-3xl font-black text-white mb-4">{{ number_format($totalTransactions) }} <span class="text-sm font-normal text-gray-500">trx</span></h3>
                    <a href="{{ route('member.transactions') }}" class="inline-flex items-center text-xs font-bold text-blue-400 hover:text-blue-300 transition">
                        Lihat Riwayat <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>

                <!-- Pengeluaran -->
                <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl p-6 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-chart-line text-6xl text-emerald-500"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-400 mb-1">Total Pengeluaran</p>
                    <h3 class="text-3xl font-black text-white mb-4">Rp {{ number_format($totalSpent, 0, ',', '.') }}</h3>
                    <p class="text-xs text-gray-500">Dari pesanan sukses</p>
                </div>
            </div>

            <!-- Two Column Layout: Recent Transactions & Favorites -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
                
                <!-- Recent Transactions -->
                <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl overflow-hidden shadow-lg flex flex-col h-full">
                    <div class="p-5 border-b border-[#2d3748] flex justify-between items-center bg-[#252b43]">
                        <h3 class="font-bold text-white"><i class="fas fa-clock text-[#f97316] mr-2"></i> Transaksi Terakhir</h3>
                        <a href="{{ route('member.transactions') }}" class="text-xs font-semibold text-[#f97316] hover:text-[#ea580c]">Lihat Semua</a>
                    </div>
                    
                    <div class="p-0 flex-grow">
                        @forelse($recentTransactions as $trx)
                            <a href="{{ route('member.transactions.show', $trx->invoice_number) }}" class="block p-4 border-b border-[#2d3748] hover:bg-[#252b43] transition-colors last:border-0">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="text-sm font-bold text-white">{{ $trx->game_name ?? 'Pesanan' }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $trx->invoice_number }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-[#f97316]">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</p>
                                        
                                        @if($trx->transaction_status == 'success')
                                            <span class="inline-block mt-1 px-2 py-0.5 bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 rounded text-[10px] font-bold">SUKSES</span>
                                        @elseif($trx->transaction_status == 'pending' || $trx->transaction_status == 'processing')
                                            <span class="inline-block mt-1 px-2 py-0.5 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded text-[10px] font-bold">PROSES</span>
                                        @else
                                            <span class="inline-block mt-1 px-2 py-0.5 bg-red-500/10 text-red-500 border border-red-500/20 rounded text-[10px] font-bold">GAGAL</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-[10px] text-gray-500">
                                    {{ $trx->created_at->format('d M Y, H:i') }}
                                </div>
                            </a>
                        @empty
                            <div class="p-8 text-center flex-grow flex flex-col justify-center items-center">
                                <i class="fas fa-shopping-basket text-4xl text-gray-600 mb-3"></i>
                                <p class="text-gray-400 text-sm">Belum ada transaksi.</p>
                                <a href="{{ route('front.index') }}" class="mt-4 px-4 py-2 bg-[#f97316] hover:bg-[#ea580c] text-white text-xs font-bold rounded-lg transition">Mulai Belanja</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Favorite Games -->
                <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl overflow-hidden shadow-lg flex flex-col h-full">
                    <div class="p-5 border-b border-[#2d3748] flex justify-between items-center bg-[#252b43]">
                        <h3 class="font-bold text-white"><i class="fas fa-heart text-pink-500 mr-2"></i> Game Favorit</h3>
                        <a href="{{ route('member.favorites') }}" class="text-xs font-semibold text-[#f97316] hover:text-[#ea580c]">Kelola</a>
                    </div>
                    
                    <div class="p-5 flex-grow">
                        @if($favorites->count() > 0)
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                @foreach($favorites->take(8) as $fav)
                                    <a href="{{ route('front.category', $fav->category->slug ?? $fav->category->id) }}" class="block group relative rounded-xl overflow-hidden border border-[#2d3748] hover:border-[#f97316] transition-all aspect-square">
                                        @if($fav->category->thumbnail)
                                            <img src="{{ asset('storage/' . $fav->category->thumbnail) }}" alt="{{ $fav->category->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full bg-[#252b43] flex items-center justify-center">
                                                <i class="fas fa-gamepad text-2xl text-gray-500"></i>
                                            </div>
                                        @endif
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                        <div class="absolute bottom-0 left-0 right-0 p-2 text-center">
                                            <p class="text-[10px] font-bold text-white truncate drop-shadow-md">{{ $fav->category->name }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="p-8 text-center flex-grow flex flex-col justify-center items-center h-full">
                                <i class="fas fa-heart-broken text-4xl text-gray-600 mb-3"></i>
                                <p class="text-gray-400 text-sm">Belum ada game favorit.</p>
                                <p class="text-gray-500 text-xs mt-1">Klik ikon ❤️ pada halaman game untuk menambahkan ke favorit.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
