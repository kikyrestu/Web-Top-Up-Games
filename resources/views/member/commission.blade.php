@extends('layouts.front')
@section('title', 'Komisi & Referral')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-black text-white">💰 Komisi & Referral</h1>
        <p class="text-gray-400 text-sm mt-1">Ajak temanmu, dapatkan komisi otomatis dari setiap transaksi!</p>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-900/30 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl text-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 bg-red-900/30 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-xl p-5">
            <p class="text-xs text-gray-400 font-bold uppercase mb-1">Saldo Komisi</p>
            <p class="text-2xl font-black text-[#f97316]">Rp {{ number_format($stats['commission_balance'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-xl p-5">
            <p class="text-xs text-gray-400 font-bold uppercase mb-1">Total Diterima</p>
            <p class="text-2xl font-black text-emerald-400">Rp {{ number_format($stats['total_earned'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-xl p-5">
            <p class="text-xs text-gray-400 font-bold uppercase mb-1">Total Referral</p>
            <p class="text-2xl font-black text-blue-400">{{ $stats['total_referrals'] }} <span class="text-sm font-normal text-gray-400">orang</span></p>
        </div>
        <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-xl p-5">
            <p class="text-xs text-gray-400 font-bold uppercase mb-1">Referral Aktif</p>
            <p class="text-2xl font-black text-purple-400">{{ $stats['active_referrals'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Referral Link Card -->
        <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-xl p-6">
            <h3 class="font-black text-white text-lg mb-1 flex items-center gap-2">
                <i class="fas fa-link text-[#f97316]"></i> Kode & Link Referral Kamu
            </h3>
            <p class="text-xs text-gray-400 mb-4">Bagikan link ini ke teman kamu. Kamu dapat bonus tiap transaksi mereka!</p>

            <div class="mb-3">
                <label class="text-xs text-gray-400 uppercase font-bold mb-1 block">Kode Referral</label>
                <div class="flex items-center gap-2">
                    <span id="ref-code" class="bg-[#121212] border border-[#333] text-[#f97316] font-black text-xl tracking-widest px-4 py-2 rounded-lg flex-1 text-center select-all">{{ $referralCode }}</span>
                    <button onclick="copyText('{{ $referralCode }}', this)" class="px-4 py-2 bg-[#f97316]/20 text-[#f97316] border border-[#f97316]/30 rounded-lg hover:bg-[#f97316]/30 transition font-bold text-sm">
                        <i class="fas fa-copy mr-1"></i>Copy
                    </button>
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-400 uppercase font-bold mb-1 block">Link Referral</label>
                <div class="flex items-center gap-2">
                    <input type="text" value="{{ $referralUrl }}" readonly class="bg-[#121212] border border-[#333] text-gray-300 text-xs px-3 py-2 rounded-lg flex-1 focus:outline-none select-all">
                    <button onclick="copyText('{{ $referralUrl }}', this)" class="px-4 py-2 bg-[#f97316]/20 text-[#f97316] border border-[#f97316]/30 rounded-lg hover:bg-[#f97316]/30 transition font-bold text-sm whitespace-nowrap">
                        <i class="fas fa-copy mr-1"></i>Copy
                    </button>
                </div>
            </div>

            <div class="mt-4 bg-[#f97316]/5 border border-[#f97316]/20 rounded-lg p-3 text-xs text-gray-400">
                <i class="fas fa-info-circle text-[#f97316] mr-1"></i>
                Setiap kali teman kamu bertransaksi, kamu mendapat bonus komisi otomatis!
            </div>
        </div>

        <!-- Withdraw Card -->
        <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-xl p-6">
            <h3 class="font-black text-white text-lg mb-1 flex items-center gap-2">
                <i class="fas fa-wallet text-emerald-400"></i> Tarik Saldo Komisi ke Wallet
            </h3>
            <p class="text-xs text-gray-400 mb-4">Saldo komisi bisa dipindahkan ke Wallet dan digunakan untuk transaksi.</p>

            <div class="bg-[#121212] border border-[#2d2d2d] rounded-lg p-4 mb-4 flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-400">Saldo Komisi Tersedia</p>
                    <p class="text-xl font-black text-[#f97316]">Rp {{ number_format($stats['commission_balance'], 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-coins text-[#f97316] text-3xl opacity-30"></i>
            </div>

            <form action="{{ route('member.commission.withdraw') }}" method="POST">
                @csrf
                <label class="text-xs text-gray-400 uppercase font-bold mb-1 block">Jumlah Penarikan (min. Rp 10.000)</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                        <input type="number" name="amount" min="10000" max="{{ $stats['commission_balance'] }}" step="1000"
                               placeholder="10000"
                               class="w-full bg-[#121212] border border-[#333] text-white pl-10 pr-4 py-3 rounded-lg text-sm focus:outline-none focus:border-[#f97316] transition">
                    </div>
                    <button type="submit" 
                            @if($stats['commission_balance'] < 10000) disabled @endif
                            class="px-6 py-3 bg-gradient-to-r from-emerald-500 to-emerald-400 text-white font-bold rounded-lg text-sm hover:-translate-y-0.5 transform transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Tarik
                    </button>
                </div>
                @if($stats['commission_balance'] < 10000)
                <p class="text-xs text-gray-500 mt-2"><i class="fas fa-lock mr-1"></i>Saldo komisi minimal Rp 10.000 untuk dapat ditarik.</p>
                @endif
            </form>
        </div>
    </div>

    <!-- Commission History -->
    <div class="bg-[#1c1c1c] border border-[#2d2d2d] rounded-xl overflow-hidden">
        <div class="bg-[#151515] px-6 py-4 border-b border-[#2d2d2d]">
            <h3 class="font-black text-white flex items-center gap-2">
                <i class="fas fa-history text-[#f97316]"></i> Riwayat Komisi
            </h3>
        </div>

        @if($commissions->isEmpty())
        <div class="text-center py-12 text-gray-500">
            <i class="fas fa-coins text-4xl mb-3 opacity-30"></i>
            <p class="text-sm">Belum ada riwayat komisi.</p>
            <p class="text-xs text-gray-600 mt-1">Mulai transaksi atau ajak teman untuk mendapat komisi!</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-[#151515] text-xs text-gray-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2d2d2d]">
                    @foreach($commissions as $commission)
                    <tr class="hover:bg-[#252525] transition">
                        <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">
                            {{ $commission->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            @if($commission->type === 'transaction')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-900/30 text-blue-400 border border-blue-800">Transaksi</span>
                            @elseif($commission->type === 'referral_bonus')
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-900/30 text-purple-400 border border-purple-800">Referral</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-800 text-gray-400 border border-gray-700">Penarikan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-300 text-xs">{{ Str::limit($commission->note, 50) ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-bold font-mono {{ $commission->amount >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $commission->amount >= 0 ? '+' : '' }}Rp {{ number_format(abs($commission->amount), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($commission->status === 'approved')
                                <span class="text-emerald-400 text-xs font-bold">✅ Aktif</span>
                            @elseif($commission->status === 'paid')
                                <span class="text-blue-400 text-xs font-bold">💸 Dibayar</span>
                            @else
                                <span class="text-amber-400 text-xs font-bold">⏳ Pending</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-[#2d2d2d]">
            {{ $commissions->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i>Disalin!';
        btn.classList.add('bg-emerald-500/20', 'text-emerald-400', 'border-emerald-500/30');
        setTimeout(() => {
            btn.innerHTML = original;
            btn.classList.remove('bg-emerald-500/20', 'text-emerald-400', 'border-emerald-500/30');
        }, 2000);
    });
}
</script>
@endpush
@endsection
