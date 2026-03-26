@extends('layouts.admin')

@section('title', 'Hasil Scraping')
@section('header', 'Produk Hasil Scraping')

@section('content')
<div x-data="scrapedApp()">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-dark-800 border border-dark-600 rounded-xl p-4 text-center">
            <p class="text-2xl font-black text-white">{{ number_format($stats['total']) }}</p>
            <p class="text-xs text-gray-400 font-bold uppercase">Total</p>
        </div>
        <div class="bg-dark-800 border border-dark-600 rounded-xl p-4 text-center">
            <p class="text-2xl font-black text-emerald-400">{{ number_format($stats['imported']) }}</p>
            <p class="text-xs text-gray-400 font-bold uppercase">Imported</p>
        </div>
        <div class="bg-dark-800 border border-dark-600 rounded-xl p-4 text-center">
            <p class="text-2xl font-black text-amber-400">{{ number_format($stats['new']) }}</p>
            <p class="text-xs text-gray-400 font-bold uppercase">Belum Import</p>
        </div>
        <div class="bg-dark-800 border border-dark-600 rounded-xl p-4 text-center">
            <p class="text-2xl font-black text-cyan-400">{{ number_format($stats['available']) }}</p>
            <p class="text-xs text-gray-400 font-bold uppercase">Available</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-gray-400 text-[10px] font-bold uppercase mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-lg p-2">
            </div>
            <div class="min-w-[150px]">
                <label class="block text-gray-400 text-[10px] font-bold uppercase mb-1">Provider</label>
                <select name="provider_id" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-lg p-2">
                    <option value="">Semua</option>
                    @foreach($providers as $prov)
                        <option value="{{ $prov->id }}" {{ request('provider_id') == $prov->id ? 'selected' : '' }}>{{ $prov->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-gray-400 text-[10px] font-bold uppercase mb-1">Brand</label>
                <select name="brand" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-lg p-2">
                    <option value="">Semua</option>
                    @foreach($brands as $b)
                        <option value="{{ $b }}" {{ request('brand') == $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[100px]">
                <label class="block text-gray-400 text-[10px] font-bold uppercase mb-1">Tipe</label>
                <select name="type" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-lg p-2">
                    <option value="">Semua</option>
                    @foreach($types as $t)
                        <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[100px]">
                <label class="block text-gray-400 text-[10px] font-bold uppercase mb-1">Status</label>
                <select name="status" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-lg p-2">
                    <option value="">Semua</option>
                    <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>🆕 Baru</option>
                    <option value="imported" {{ request('status') == 'imported' ? 'selected' : '' }}>✅ Imported</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-brand-500/20 text-brand-400 border border-brand-500/30 rounded-lg text-sm font-bold hover:bg-brand-500/30 transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.scraped-products.index') }}" class="px-4 py-2 bg-dark-700 text-gray-400 rounded-lg text-sm font-bold hover:text-white transition">Reset</a>
        </form>
    </div>

    {{-- Import Form --}}
    <form method="POST" action="{{ route('admin.scraped-products.import') }}" id="importForm">
        @csrf

        {{-- Action Bar --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                    <input type="checkbox" @change="toggleAll($event)" class="rounded border-dark-600 bg-dark-900 text-brand-500 focus:ring-brand-500/40">
                    <span>Pilih Semua</span>
                </label>
                <span class="text-xs text-gray-500" x-show="selectedIds.length > 0" x-text="selectedIds.length + ' dipilih'"></span>
            </div>
            <button type="submit" :disabled="selectedIds.length === 0" class="bg-gradient-to-r from-emerald-500 to-emerald-400 text-white font-bold py-2 px-6 rounded-xl shadow-lg disabled:opacity-30 disabled:cursor-not-allowed hover:-translate-y-0.5 transform transition-all flex items-center gap-2">
                <i class="fas fa-download"></i>
                Import Terpilih
            </button>
        </div>

        {{-- Table --}}
        <div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl overflow-x-auto">
            <table class="min-w-full divide-y divide-dark-600">
                <thead class="bg-dark-900/50">
                    <tr>
                        <th class="px-3 py-3 w-10"></th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Status</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Provider</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Kode SKU</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Nama Produk</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Brand</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-gray-400 uppercase">Harga Modal</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-gray-400 uppercase">Harga Jual (est)</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Status API</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700 text-sm">
                    @forelse($scrapedProducts as $sp)
                    <tr class="hover:bg-dark-700/30 transition {{ $sp->is_imported ? 'opacity-60' : '' }}">
                        <td class="px-3 py-2">
                            @if(!$sp->is_imported)
                            <input type="checkbox" name="ids[]" value="{{ $sp->id }}" x-model="selectedIds" class="rounded border-dark-600 bg-dark-900 text-brand-500 focus:ring-brand-500/40">
                            @else
                            <span class="text-emerald-400 text-xs"><i class="fas fa-check-circle"></i></span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @if($sp->is_imported)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-900/30 text-emerald-400 border border-emerald-800">✅ Imported</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-900/30 text-amber-400 border border-amber-800">🆕 Baru</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-300 text-xs">{{ $sp->apiProvider?->name ?? '-' }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-cyan-400">{{ $sp->provider_product_code }}</td>
                        <td class="px-3 py-2 text-white font-medium">{{ Str::limit($sp->product_name, 40) }}</td>
                        <td class="px-3 py-2 text-gray-300">{{ $sp->brand }}</td>
                        <td class="px-3 py-2 text-right text-white font-mono">Rp {{ number_format($sp->price, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right text-emerald-400 font-mono">Rp {{ number_format($sp->price_sell_suggestion, 0, ',', '.') }}</td>
                        <td class="px-3 py-2">
                            @if($sp->status_provider === 'available')
                                <span class="text-emerald-400 text-xs font-bold">● Available</span>
                            @elseif($sp->status_provider === 'disturb')
                                <span class="text-amber-400 text-xs font-bold">● Gangguan</span>
                            @else
                                <span class="text-red-400 text-xs font-bold">● Empty</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-500 italic">
                            Belum ada produk. <a href="{{ route('admin.product-sync.index') }}" class="text-brand-400 hover:underline">Sync dulu →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $scrapedProducts->links() }}
    </div>
</div>

@push('scripts')
<script>
function scrapedApp() {
    return {
        selectedIds: [],
        toggleAll(event) {
            if (event.target.checked) {
                this.selectedIds = Array.from(document.querySelectorAll('input[name="ids[]"]')).map(el => el.value);
            } else {
                this.selectedIds = [];
            }
        }
    };
}
</script>
@endpush
@endsection
