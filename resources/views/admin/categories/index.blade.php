@extends('layouts.admin')

@section('title', 'Manajemen Kategori')
@section('header', 'Manajemen Kategori')

@section('content')
<div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white mb-4 sm:mb-0">Daftar Kategori</h2>
        <a href="{{ route('admin.categories.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm transition">
            <i class="fas fa-plus mr-2"></i> Tambah Kategori
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-900 text-gray-300 text-sm border-b border-gray-700">
                      <th class="py-3 px-4 w-24">Urutan</th>
                      <th class="py-3 px-4 w-24">Thumbnail</th>
                      <th class="py-3 px-4">Info Kategori</th>
                      <th class="py-3 px-4">Tags (Menu)</th>
                      <th class="py-3 px-4 text-center">Status</th>
                      <th class="py-3 px-4 text-center w-32">Aksi</th>
                  </tr>
              </thead>
              <tbody id="sortable-categories">
                  @forelse($categories as $index => $category)
                  <tr class="border-b border-gray-700 hover:bg-gray-900/50 text-sm align-middle bg-gray-800" data-id="{{ $category->id }}">
                      <td class="py-3 px-4">
                          <div class="flex items-center">
                              <i class="fas fa-grip-lines text-gray-500 cursor-move mr-3 hover:text-indigo-400 p-2"></i>
                              <span class="sort-number text-gray-400 font-mono">{{ $index + 1 }}</span>
                          </div>
                      </td>
                    <td class="py-3 px-4">
                        @if($category->thumbnail)
                            <img src="{{ Storage::url($category->thumbnail) }}" alt="{{ $category->name }}" class="h-12 w-12 object-cover rounded shadow border">
                        @elseif($category->icon)
                            <div class="h-12 w-12 flex items-center justify-center bg-gray-700 rounded shadow border text-gray-400">
                                <i class="{{ $category->icon }} text-xl"></i>
                            </div>
                        @else
                            <div class="h-12 w-12 flex items-center justify-center bg-gray-700 rounded shadow border text-gray-400 text-xs text-center border-dashed">
                                N/A
                            </div>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <div class="font-bold text-white">{{ $category->name }}</div>
                        <div class="text-xs text-gray-400">{{ $category->publisher ?? 'No Publisher' }}</div>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex gap-1 flex-wrap">
                            @if($category->is_popular)
                                <span class="bg-amber-900/30 text-amber-400 border border-amber-800 py-0.5 px-2 rounded-full text-[10px] font-bold border border-yellow-200">POPULER</span>
                            @endif
                            @if($category->is_new)
                                <span class="bg-emerald-900/30 text-emerald-400 border border-emerald-800 py-0.5 px-2 rounded-full text-[10px] font-bold border border-green-200">BARU</span>
                            @endif
                            @if(!$category->is_popular && !$category->is_new)
                                <span class="text-gray-400 text-xs italic">-</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-4 text-center">
                        @if($category->is_active)
                            <span class="bg-indigo-900/30 text-indigo-400 border border-indigo-800 py-1 px-2 rounded text-xs font-semibold">Aktif</span>
                        @else
                            <span class="bg-rose-900/30 text-rose-400 border border-rose-800 py-1 px-2 rounded text-xs font-semibold">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center flex justify-center space-x-3 items-center pt-5 border-t-0">
                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="text-blue-500 hover:text-blue-700 transition" title="Edit">
                            <i class="fas fa-edit text-lg"></i>
                        </a>
                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini beserta gambar thumbnail-nya?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                                <i class="fas fa-trash text-lg"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-6 px-4 text-center text-gray-400 italic">Belum ada kategori yang ditambahkan. <a href="{{ route('admin.categories.create') }}" class="text-blue-500 font-bold hover:underline">Tambah Kategori</a></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tbody = document.getElementById('sortable-categories');
        
        if (tbody) {
            new Sortable(tbody, {
                animation: 150,
                handle: '.cursor-move',
                ghostClass: 'bg-gray-700',
                onEnd: function () {
                    // Update index numbers visually
                    document.querySelectorAll('.sort-number').forEach((el, index) => {
                        el.textContent = index + 1;
                    });

                    // Gather IDs in the new order
                    let order = [];
                    document.querySelectorAll('#sortable-categories tr').forEach(row => {
                        if (row.dataset.id) {
                            order.push(row.dataset.id);
                        }
                    });

                    // Send to server
                    fetch('{{ route('admin.categories.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order: order })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Optionally show a small toast or notification
                            console.log('Order updated successfully');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating order:', error);
                        alert('Terjadi kesalahan saat mengurutkan kategori.');
                    });
                }
            });
        }
    });
</script>
@endpush
@endsection
