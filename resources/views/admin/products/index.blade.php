@extends('layouts.admin')

@section('title', 'Manajemen Produk')
@section('header', 'Manajemen Produk')

@section('content')
<div class="glass-panel rounded-2xl shadow-xl border border-dark-700 p-6 relative overflow-hidden mb-8" x-data="bulkDeleteApp({{ $products->pluck('id')->toJson() }})">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-xl font-bold tracking-tight text-white border-l-4 border-brand-500 pl-3">Daftar Produk</h2>
        
        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 mt-4 sm:mt-0">
            <form action="{{ route('admin.products.index') }}" method="GET" class="w-full sm:w-64 shrink-0">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="bg-dark-800 border border-dark-600 text-white text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full pl-10 p-2.5 shadow-sm transition-all">
                    @if(request('search'))
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <a href="{{ route('admin.products.index') }}" class="text-gray-500 hover:text-red-400">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </form>
            
            <form x-show="selectedIds.length > 0" x-transition action="{{ route('admin.products.destroy-bulk') }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ' + selectedIds.length + ' produk terpilih?');" class="flex items-center">
                @csrf
                @method('DELETE')
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="selected_ids[]" :value="id">
                </template>
                <button type="submit" class="bg-red-500/10 hover:bg-red-500/20 text-red-500 hover:text-red-400 border border-red-500/20 py-2.5 px-4 rounded-xl font-semibold transition flex items-center shadow-[0_0_15px_rgba(239,68,68,0.1)] text-sm">
                    <i class="fas fa-trash-alt mr-2"></i> Hapus Terpilih (<span x-text="selectedIds.length"></span>)
                </button>
            </form>

            <form action="{{ route('admin.product-sync.sync') }}" method="POST" class="shrink-0" onsubmit="return confirm('Sinkronisasi akan meng-update Harga Dasar dan menyesuaikan Harga Jual semua produk PPOB. Proses memakan waktu puluhan detik. Lanjutkan?')">
                @csrf
                <button type="submit" class="bg-dark-600 hover:bg-dark-500 border border-dark-500 text-white font-semibold py-2.5 px-4 rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center shrink-0 whitespace-nowrap">
                    <i class="fas fa-sync-alt mr-2 text-sm text-yellow-400"></i> Sync Harga
                </button>
            </form>

            <a href="{{ route('admin.products.create') }}" class="bg-gradient-to-r from-brand-500 to-brand-400 hover:from-brand-400 hover:to-brand-500 text-white font-semibold py-2.5 px-5 rounded-xl shadow-lg shadow-brand-500/30 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center shrink-0 whitespace-nowrap">
                <i class="fas fa-plus mr-2 text-sm"></i> Tambah Produk
            </a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-dark-700 shadow-inner">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-dark-800 text-gray-400 text-xs uppercase tracking-wider font-semibold">
                    <th class="py-4 px-5 border-b border-dark-700 w-10">
                        <div class="flex items-center">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll" class="w-4 h-4 rounded border-dark-500 bg-dark-900 text-brand-500 focus:ring-brand-500/50 cursor-pointer">
                        </div>
                    </th>
                    <th class="py-4 px-5 border-b border-dark-700">Gambar</th>
                    <th class="py-4 px-5 border-b border-dark-700">Nama Produk</th>
                    <th class="py-4 px-5 border-b border-dark-700">Kategori</th>
                    <th class="py-4 px-5 border-b border-dark-700">Kode Provider</th>
                    <th class="py-4 px-5 border-b border-dark-700">Harga Modal</th>
                    <th class="py-4 px-5 border-b border-dark-700">Harga Jual</th>
                    <th class="py-4 px-5 border-b border-dark-700 text-center">Status</th>
                    <th class="py-4 px-5 border-b border-dark-700 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700/50">
                @forelse($products as $product)
                <tr class="hover:bg-dark-800/50 text-sm transition duration-150 group" :class="{ 'bg-brand-500/5': selectedIds.includes({{ $product->id }}) }">
                    <td class="py-3 px-5">
                        <div class="flex items-center">
                            <input type="checkbox" value="{{ $product->id }}" x-model="selectedIds" class="w-4 h-4 rounded border-dark-500 bg-dark-900 text-brand-500 focus:ring-brand-500/50 cursor-pointer">
                        </div>
                    </td>
                    <td class="py-3 px-5">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="w-12 h-12 object-cover rounded-xl shadow-md border border-dark-600 transition group-hover:scale-105">
                        @else
                            <div class="w-12 h-12 bg-dark-700 rounded-xl flex items-center justify-center text-gray-500 border border-dark-600 shadow-inner transition group-hover:bg-dark-600">
                                <i class="fas fa-image text-lg"></i>
                            </div>
                        @endif
                    </td>
                    <td class="py-3 px-5 font-semibold text-white">{{ $product->name }}</td>
                    <td class="py-3 px-5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-dark-700 text-gray-300 border border-dark-600">
                            {{ $product->category->name ?? '-' }}
                        </span>
                    </td>
                    <td class="py-3 px-5 text-gray-400 font-mono text-xs">
                        @if($product->providerMappings->isEmpty())
                            -
                        @else
                            {{ $product->providerMappings->sortBy('price_capital')->take(3)->map(function ($mapping) {
                                $providerCode = strtoupper($mapping->apiProvider->code ?? 'provider');
                                return $providerCode . ':' . $mapping->provider_product_code;
                            })->implode(', ') }}
                        @endif
                    </td>
                    <td class="py-3 px-5 font-medium text-gray-400">Rp {{ number_format($product->price_capital, 0, ',', '.') }}</td>
                    <td class="py-3 px-5 text-brand-400 font-bold">Rp {{ number_format($product->price_sell, 0, ',', '.') }}</td>
                    <td class="py-3 px-5 text-center">
                        @if($product->is_active)
                            @if(($product->status_provider ?? 'available') === 'available')
                                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 py-1 px-3 rounded-full text-xs font-bold inline-flex items-center gap-1.5 shadow-[0_0_10px_rgba(16,185,129,0.1)]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif
                                </span>
                            @else
                                <span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 py-1 px-3 rounded-full text-xs font-bold inline-flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Gangguan
                                </span>
                            @endif
                        @else
                            <span class="bg-red-500/10 text-red-400 border border-red-500/20 py-1 px-3 rounded-full text-xs font-bold inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="py-3 px-5 text-center flex justify-center space-x-2 mt-1">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="w-8 h-8 rounded-lg flex items-center justify-center bg-dark-700 hover:bg-brand-500/20 text-gray-400 hover:text-brand-400 transition-colors border border-transparent hover:border-brand-500/30" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus produk ini?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-dark-700 hover:bg-red-500/20 text-gray-400 hover:text-red-400 transition-colors border border-transparent hover:border-red-500/30" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-8 px-6 text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center space-y-3">
                            <div class="w-16 h-16 rounded-full bg-dark-800 flex items-center justify-center border border-dark-700 shadow-inner">
                                <i class="fas fa-box-open text-2xl text-gray-600"></i>
                            </div>
                            <p>Belum ada produk yang ditambahkan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6 flex justify-end">
        {{ $products->links() }}
    </div>
</div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bulkDeleteApp', (allIds) => ({
        selectedIds: [],
        selectAll: false,
        allIds: allIds,
        toggleAll() {
            if (this.selectAll) {
                this.selectedIds = [...this.allIds];
            } else {
                this.selectedIds = [];
            }
        }
    }))
})
</script>
@endpush
@endsection