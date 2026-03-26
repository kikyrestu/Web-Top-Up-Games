@extends('layouts.admin')

@section('title', 'Sync Produk')
@section('header', 'Sync Produk dari Provider')

@section('content')
<div class="space-y-6" x-data="productSyncApp()">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-dark-800 border border-dark-600 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-brand-500/20 rounded-lg flex items-center justify-center text-brand-400"><i class="fas fa-database"></i></div>
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase">Total Produk Terscrape</p>
                    <p class="text-xl font-bold text-white">{{ number_format($syncStats['total']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-dark-800 border border-dark-600 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-500/20 rounded-lg flex items-center justify-center text-emerald-400"><i class="fas fa-check-circle"></i></div>
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase">Sudah Diimport</p>
                    <p class="text-xl font-bold text-white">{{ number_format($syncStats['imported']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-dark-800 border border-dark-600 rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center text-amber-400"><i class="fas fa-sparkles"></i></div>
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase">Belum Diimport</p>
                    <p class="text-xl font-bold text-white">{{ number_format($syncStats['new']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Sync Panel --}}
    <div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl p-6 md:p-8">
        <h3 class="text-xl font-black text-white mb-2">Sync Produk dari Provider</h3>
        <p class="text-sm text-gray-400 mb-6">Pilih provider lalu klik sync untuk menarik semua daftar produk terbaru dari API provider.</p>

        <form method="POST" action="{{ route('admin.product-sync.sync') }}" @submit="syncing = true">
            @csrf
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Pilih Provider</label>
                    <select name="provider_id" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                        <option value="">🔄 Sync Semua Provider</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" {{ !$provider->supports_sync ? 'disabled' : '' }}>
                                {{ $provider->name }} {{ !$provider->supports_sync ? '(belum support)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" :disabled="syncing" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 transform transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <template x-if="!syncing">
                        <span><i class="fas fa-sync-alt mr-2"></i>Sync Sekarang</span>
                    </template>
                    <template x-if="syncing">
                        <span><i class="fas fa-spinner fa-spin mr-2"></i>Sedang Sync...</span>
                    </template>
                </button>
            </div>
        </form>

        @if($lastSync)
            <p class="text-xs text-gray-500 mt-4">
                <i class="far fa-clock mr-1"></i> Terakhir sync: {{ \Carbon\Carbon::parse($lastSync)->diffForHumans() }}
                ({{ \Carbon\Carbon::parse($lastSync)->format('d/m/Y H:i') }})
            </p>
        @endif
    </div>

    {{-- Quick Link --}}
    @if($syncStats['total'] > 0)
    <div class="text-center">
        <a href="{{ route('admin.scraped-products.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-dark-800 hover:bg-brand-500/10 border border-dark-600 hover:border-brand-500/50 rounded-xl text-sm font-semibold text-gray-300 hover:text-white transition">
            <i class="fas fa-list text-brand-400"></i>
            Lihat Hasil Scraping ({{ number_format($syncStats['total']) }} produk)
            <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script>
function productSyncApp() {
    return { syncing: false };
}
</script>
@endpush
@endsection
