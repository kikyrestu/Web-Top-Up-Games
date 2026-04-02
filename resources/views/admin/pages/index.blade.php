@extends('layouts.admin')

@section('title', 'Kelola Halaman Statis')
@section('header', 'Halaman Statis')

@section('content')
<div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h3 class="text-xl font-black text-white">Daftar Halaman</h3>
            <p class="text-sm text-gray-400">Atur konten Syarat Ketentuan, Privasi, FAQ, dsb.</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2 px-4 rounded-xl shadow-lg hover:-translate-y-0.5 transition">
            <i class="fas fa-plus mr-2"></i> Buat Halaman
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 p-4 rounded-xl mb-6">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-dark-900/50 text-gray-400 text-xs uppercase tracking-wider">
                    <th class="p-4 rounded-tl-xl">ID</th>
                    <th class="p-4">Judul Halaman</th>
                    <th class="p-4">Slug URL</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right rounded-tr-xl">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-dark-700/50">
                @forelse($pages as $page)
                <tr class="hover:bg-dark-900/30 transition">
                    <td class="p-4 text-gray-500 font-mono">#{{ $page->id }}</td>
                    <td class="p-4 font-bold text-white">{{ $page->title }}</td>
                    <td class="p-4 text-brand-400">/halaman/{{ $page->slug }}</td>
                    <td class="p-4">
                        @if($page->is_active)
                            <span class="bg-emerald-500/20 text-emerald-400 py-1 px-3 rounded-full text-xs font-bold border border-emerald-500/30">Aktif</span>
                        @else
                            <span class="bg-rose-500/20 text-rose-400 py-1 px-3 rounded-full text-xs font-bold border border-rose-500/30">Draft</span>
                        @endif
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('front.page', $page->slug) }}" target="_blank" class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center hover:bg-blue-500 hover:text-white transition" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.pages.edit', $page) }}" class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center hover:bg-amber-500 hover:text-white transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus halaman ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center hover:bg-rose-500 hover:text-white transition" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-500">Belum ada halaman.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $pages->links() }}
    </div>
</div>
@endsection
