@extends('layouts.front')

@section('title', 'Dompet ' . $walletLabel)

@section('content')
<div class="max-w-[1280px] mx-auto px-4 mt-8">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar -->
        <div class="w-full lg:w-1/4">
            <x-member-sidebar />
        </div>

        <!-- Main Content -->
        <div class="w-full lg:w-3/4 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 p-4 rounded-xl flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-500/10 border border-red-500/20 text-red-500 p-4 rounded-xl flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                </div>
            @endif

            <!-- Balance Card -->
            <div class="bg-gradient-to-br from-[#1d2235] to-[#f97316]/10 border border-[#f97316]/20 rounded-2xl shadow-lg overflow-hidden relative p-8">
                <!-- Background Decoration -->
                <div class="absolute -right-10 -bottom-10 opacity-10">
                    <i class="fas fa-wallet text-9xl text-[#f97316]"></i>
                </div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between w-full h-full items-start md:items-center gap-6">
                    <div>
                        <p class="text-sm font-semibold text-gray-400 uppercase tracking-widest mb-1">Saldo {{ $walletLabel }} Anda</p>
                        <h2 class="text-4xl md:text-5xl font-black text-white">Rp {{ number_format($user->wallet_balance, 0, ',', '.') }}</h2>
                        <p class="text-xs text-gray-500 mt-2"><i class="fas fa-info-circle mr-1"></i> Gunakan saldo ini untuk belanja lebih cepat tanpa ribet transfer.</p>
                    </div>
                    
                    <button onclick="document.getElementById('topup-modal').classList.remove('hidden')" class="px-8 py-4 bg-gradient-to-r from-[#f97316] to-[#ea580c] hover:from-[#ea580c] hover:to-[#c2410c] text-white font-bold rounded-xl shadow-[0_0_20px_rgba(249,115,22,0.4)] transition-all flex items-center gap-2 shrink-0">
                        <i class="fas fa-plus-circle"></i> Top Up Saldo
                    </button>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl shadow-lg overflow-hidden flex flex-col min-h-[400px]">
                <div class="p-6 border-b border-[#2d3748] bg-[#252b43] flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white"><i class="fas fa-list-ul text-[#f97316] mr-2"></i> Riwayat {{ $walletLabel }}</h2>
                </div>

                <div class="flex-grow flex flex-col">
                    @forelse($transactions as $trx)
                        <div class="p-6 border-b border-[#2d3748] hover:bg-[#252b43] transition-colors last:border-0 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <!-- Icon Based on Type -->
                                @if($trx->type == 'topup')
                                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 flex items-center justify-center shrink-0">
                                        <i class="fas fa-arrow-down text-xl"></i>
                                    </div>
                                @elseif($trx->type == 'purchase')
                                    <div class="w-12 h-12 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 flex items-center justify-center shrink-0">
                                        <i class="fas fa-arrow-up text-xl"></i>
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-500 flex items-center justify-center shrink-0">
                                        <i class="fas fa-undo-alt text-xl"></i>
                                    </div>
                                @endif

                                <div>
                                    <h4 class="font-bold text-white text-sm md:text-base">
                                        @if($trx->type == 'topup') Top Up Saldo
                                        @elseif($trx->type == 'purchase') Pembayaran Pesanan
                                        @else Pengembalian Dana
                                        @endif
                                    </h4>
                                    <p class="text-xs text-gray-400">{{ $trx->reference }}</p>
                                    <p class="text-[10px] text-gray-500 mt-1">{{ $trx->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <p class="font-black text-lg md:text-xl {{ $trx->type == 'purchase' ? 'text-red-500' : 'text-emerald-500' }}">
                                    {{ $trx->type == 'purchase' ? '-' : '+' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-gray-500">Sisa: Rp {{ number_format($trx->balance_after, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="flex-grow flex flex-col justify-center items-center py-16 text-center">
                            <i class="fas fa-receipt text-6xl text-gray-600 mb-4"></i>
                            <h3 class="text-lg font-bold text-white mb-2">Belum ada mutasi saldo</h3>
                            <p class="text-gray-400 text-sm max-w-sm">Riwayat penambahan dan penggunaan saldo Anda akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>

                @if($transactions->hasPages())
                    <div class="p-6 border-t border-[#2d3748] bg-[#1d2235]">
                        {{ $transactions->links('vendor.pagination.tailwind-dark') }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- Modal Top Up -->
<div id="topup-modal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-[#1d2235] border border-[#2d3748] rounded-2xl w-full max-w-md shadow-[0_0_40px_rgba(0,0,0,0.8)] overflow-hidden">
        
        <div class="p-5 border-b border-[#2d3748] flex justify-between items-center bg-[#252b43]">
            <h3 class="font-bold text-white text-lg"><i class="fas fa-plus-circle text-[#f97316] mr-2"></i> Top Up {{ $walletLabel }}</h3>
            <button onclick="document.getElementById('topup-modal').classList.add('hidden')" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('member.wallet.topup') }}" method="POST" class="p-6">
            @csrf

            <!-- Amount Input -->
            <div class="mb-6">
                <label for="amount" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Nominal Top Up</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                    <input type="number" name="amount" id="amount" required min="10000" step="1000"
                        class="w-full bg-[#0f1118] border border-[#2d3748] text-white text-lg font-bold rounded-xl pl-12 pr-4 py-4 focus:outline-none focus:border-[#f97316] focus:ring-1 focus:ring-[#f97316] transition"
                        placeholder="100000">
                </div>
                <p class="text-[10px] text-gray-500 mt-2">Minimal top up Rp 10.000</p>
            </div>

            <!-- Quick Amounts Grid -->
            <div class="grid grid-cols-3 gap-3 mb-6">
                @foreach([20000, 50000, 100000, 200000, 500000, 1000000] as $quickAmount)
                    <button type="button" onclick="document.getElementById('amount').value = {{ $quickAmount }}" class="py-2 border border-[#2d3748] rounded-lg text-xs font-bold text-gray-300 hover:bg-[#f97316]/10 hover:border-[#f97316] hover:text-[#f97316] transition-colors">
                        {{ number_format($quickAmount, 0, ',', '.') }}
                    </button>
                @endforeach
            </div>

            <!-- Payment Method Select -->
            @if($paymentGateways->count() > 0)
                <div class="mb-8">
                    <label for="payment_gateway_id" class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wider">Metode Pembayaran</label>
                    <select name="payment_gateway_id" id="payment_gateway_id" required
                        class="w-full bg-[#0f1118] border border-[#2d3748] text-white text-sm rounded-xl px-4 py-3 focus:outline-none focus:border-[#f97316] appearance-none">
                        <option value="" disabled selected>Pilih Gateway Pembayaran...</option>
                        @foreach($paymentGateways as $pg)
                            <option value="{{ $pg->id }}">{{ $pg->name }} ({{ strtoupper($pg->code) }})</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full py-4 bg-[#f97316] hover:bg-[#ea580c] text-white font-bold rounded-xl transition-all shadow-lg shadow-[#f97316]/20">
                    Lanjutkan Pembayaran
                </button>
            @else
                <div class="p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-xl mb-4">
                    <p class="text-sm text-yellow-500 text-center"><i class="fas fa-exclamation-triangle mr-1"></i> Metode pembayaran belum tersedia. Hubungi admin.</p>
                </div>
                <button type="button" disabled class="w-full py-4 bg-gray-600 text-gray-400 font-bold rounded-xl cursor-not-allowed">
                    Lanjutkan Pembayaran
                </button>
            @endif
        </form>
    </div>
</div>

@endsection
