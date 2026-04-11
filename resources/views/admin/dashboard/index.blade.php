@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard Utama')

@section('content')
{{-- Pricing Mode Badge --}}
@php $pricingMode = \App\Models\Setting::get('pricing_mode', 'manual'); @endphp
<div class="mb-6 flex items-center gap-3">
    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold {{ $pricingMode === 'cheapest_auto' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-brand-500/10 text-brand-400 border border-brand-500/30' }}">
        <i class="fas {{ $pricingMode === 'cheapest_auto' ? 'fa-bolt' : 'fa-sliders-h' }}"></i>
        Mode: {{ $pricingMode === 'cheapest_auto' ? '⚡ Termurah Auto' : '📋 Manual' }}
    </div>
    <a href="{{ route('admin.settings.index') }}" class="text-xs text-gray-500 hover:text-white transition">Ubah →</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <!-- Card 1 -->
    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 border-l-4 border-b border-gray-700lue-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-900/50 text-indigo-400 mr-4">
                <i class="fas fa-shopping-cart text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-400 font-medium">Total Transaksi</p>
                <p class="text-2xl font-bold text-white">{{ number_format($totalOrders, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 border-l-4 border-green-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-emerald-900/50 text-emerald-400 mr-4">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-400 font-medium">Transaksi Sukses</p>
                <p class="text-2xl font-bold text-white">{{ number_format($successfulOrders, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 border-l-4 border-red-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-rose-900/50 text-rose-400 mr-4">
                <i class="fas fa-box text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-400 font-medium">Total Produk</p>
                <p class="text-2xl font-bold text-white">{{ number_format($totalProducts, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Card 4 -->
    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 border-l-4 border-yellow-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-amber-900/50 text-amber-400 mr-4">
                <i class="fas fa-wallet text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-400 font-medium">Total Revenue</p>
                <p class="text-2xl font-bold text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Profit Cards Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 border-l-4 border-emerald-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-emerald-900/50 text-emerald-400 mr-4">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-400 font-medium">Total Keuntungan</p>
                <p class="text-2xl font-bold text-emerald-400">Rp {{ number_format($totalProfit, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 border-l-4 border-cyan-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-cyan-900/50 text-cyan-400 mr-4">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-400 font-medium">Total Modal</p>
                <p class="text-2xl font-bold text-white">Rp {{ number_format($totalModal, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 border-l-4 border-purple-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-900/50 text-purple-400 mr-4">
                <i class="fas fa-percentage text-xl"></i>
            </div>
            <div>
                <p class="text-sm text-gray-400 font-medium">Margin Rata-rata</p>
                <p class="text-2xl font-bold text-white">{{ $totalModal > 0 ? number_format(($totalProfit / $totalModal) * 100, 1) : '0' }}%</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-6">
    <div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 overflow-x-auto">
        <h3 class="font-bold text-lg mb-4 text-white">Update Transaksi Terbaru</h3>
        @if($recentTransactions->count() > 0)
        <table class="min-w-full divide-y divide-gray-700">
            <thead class="bg-gray-900/50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Inv</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Produk</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="bg-gray-800 text-white divide-y divide-gray-700 text-sm">
                @foreach($recentTransactions as $trx)
                <tr>
                    <td class="px-4 py-2 font-mono text-xs">{{ $trx->invoice_number }}</td>
                    <td class="px-4 py-2">{{ optional($trx->items->first()?->product)->name ?? 'Produk' }}</td>
                    <td class="px-4 py-2">
                        @if($trx->transaction_status === 'success')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-900/30 text-emerald-400 border border-emerald-800">Sukses</span>
                        @elseif($trx->transaction_status === 'pending')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-900/30 text-amber-400 border border-amber-800">Pending</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-rose-900/30 text-rose-400 border border-rose-800">Gagal</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-gray-400 text-sm italic text-center p-4">
            Belum ada transaksi saat ini.
        </div>
        @endif
    </div>

</div>
@endsection
