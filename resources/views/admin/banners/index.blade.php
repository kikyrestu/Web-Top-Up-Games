@extends('layouts.admin')

@section('content')
<div class="glass-panel rounded-2xl shadow-xl border border-dark-700 p-6 relative overflow-hidden mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold tracking-tight text-white border-l-4 border-brand-500 pl-3">Daftar Banner</h2>
        <a href="{{ route('admin.banners.create') }}" class="bg-gradient-to-r from-brand-500 to-brand-400 hover:from-brand-400 hover:to-brand-500 text-white font-semibold py-2.5 px-5 rounded-xl shadow-lg shadow-brand-500/30 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center justify-center">
            <i class="fas fa-plus mr-2 text-sm"></i> Tambah Banner
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-dark-700 shadow-inner">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-dark-800 text-gray-400 text-xs uppercase tracking-wider font-semibold">
                    <th class="py-4 px-5 border-b border-dark-700 w-20 text-center">Order</th>
                    <th class="py-4 px-5 border-b border-dark-700 text-center w-32">Media</th>
                    <th class="py-4 px-5 border-b border-dark-700">Info Banner</th>
                    <th class="py-4 px-5 border-b border-dark-700 text-center">Status</th>
                    <th class="py-4 px-5 border-b border-dark-700 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700/50">
                @forelse($banners as $banner)
                <tr class="hover:bg-dark-800/50 text-sm transition duration-150">
                    <td class="py-4 px-5 text-center font-mono text-gray-500">{{ $banner->order }}</td>
                    <td class="py-4 px-5 text-center">
                        
                        @if($banner->media_type == "image" && $banner->image)
                            <img src="{{ Storage::url($banner->image) }}" class="h-12 w-24 rounded object-cover border border-dark-600 shadow-sm">
                        @elseif($banner->media_type == "video" && $banner->media_content)
                            <video class="h-12 w-24 rounded object-cover border border-dark-600 shadow-sm" muted>
                                <source src="{{ Storage::url($banner->media_content) }}" type="video/mp4">
                            </video>
                        @elseif($banner->media_type == "embed")
                            <div class="h-12 w-24 rounded bg-dark-700 border border-dark-600 flex items-center justify-center text-red-500 shadow-sm"><i class="fab fa-youtube"></i></div>
                        @elseif($banner->media_type == "html" && $banner->media_content)
                            <div class="h-12 w-24 rounded border border-dark-600 shadow-sm overflow-hidden bg-white/5 relative"><div class="absolute inset-0 z-10 pointer-events-none"></div><iframe class="w-full h-full border-0 origin-top-left scale-[0.2]" style="width: 500%; height: 500%;" srcdoc="{{ $banner->media_content }}" sandbox="allow-same-origin"></iframe></div>
                        @else
                            <div class="h-12 w-24 rounded bg-dark-700 border border-dark-600 flex items-center justify-center text-gray-500"><i class="fas fa-image"></i></div>
                        @endif
                    </td>
                    <td class="py-4 px-5 text-center">
                        <div class="text-sm font-bold text-white mb-1">{{ $banner->title ?? '-' }}</div>
                        <div class="text-xs text-blue-500">{{ $banner->link ?? 'Tidak ada link' }}</div>
                    </td>
                    <td class="py-4 px-5 text-center">
                        @if($banner->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-900/30 text-emerald-400 border border-emerald-800">Aktif</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-rose-900/30 text-rose-400 border border-rose-800">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-4 px-5 text-center"><div class="flex justify-center w-full space-x-2"><a href="{{ route('admin.banners.edit', $banner->id) }}" class="w-8 h-8 rounded-lg flex items-center justify-center bg-dark-700 hover:bg-brand-500/20 text-gray-400 hover:text-brand-400 transition-colors border border-transparent hover:border-brand-500/30" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus banner ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-lg flex items-center justify-center bg-dark-700 hover:bg-red-500/20 text-gray-400 hover:text-red-400 transition-colors border border-transparent hover:border-red-500/30" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form></div></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-400">Belum ada banner.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
