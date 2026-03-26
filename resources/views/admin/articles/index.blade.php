@extends('layouts.admin')

@section('title', 'CMS Artikel')
@section('header', 'CMS Artikel')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-dark-800/40 border border-dark-600/50 rounded-2xl p-4">
            <p class="text-gray-400 text-xs uppercase tracking-wider">Total Artikel</p>
            <p class="text-2xl font-black text-white mt-2">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-dark-800/40 border border-dark-600/50 rounded-2xl p-4">
            <p class="text-gray-400 text-xs uppercase tracking-wider">Published</p>
            <p class="text-2xl font-black text-emerald-400 mt-2">{{ $stats['published'] }}</p>
        </div>
        <div class="bg-dark-800/40 border border-dark-600/50 rounded-2xl p-4">
            <p class="text-gray-400 text-xs uppercase tracking-wider">Draft</p>
            <p class="text-2xl font-black text-amber-400 mt-2">{{ $stats['draft'] }}</p>
        </div>
    </div>

    <div class="bg-dark-800/40 border border-dark-600/50 rounded-2xl p-4">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <form method="GET" action="{{ route('admin.articles.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 w-full lg:max-w-3xl">
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-400 mb-1">Cari Artikel</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Judul, slug, atau isi konten" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                </div>
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Status</label>
                    <select name="status" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                        <option value="">Semua</option>
                        <option value="published" {{ $filters['status'] === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="draft" {{ $filters['status'] === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="bg-brand-500 hover:bg-brand-400 text-white font-semibold text-sm px-4 py-2 rounded-xl">Filter</button>
                    <a href="{{ route('admin.articles.index') }}" class="bg-dark-700 hover:bg-dark-600 text-gray-200 font-semibold text-sm px-4 py-2 rounded-xl">Reset</a>
                </div>
            </form>

            <a href="{{ route('admin.articles.create') }}" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold text-sm px-4 py-2.5 rounded-xl shadow-lg shadow-brand-500/30 whitespace-nowrap">
                <i class="fas fa-plus mr-2"></i> Tulis Artikel
            </a>
        </div>
    </div>

    <div class="bg-dark-800/40 border border-dark-600/50 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-dark-900/60">
                    <tr class="text-xs text-gray-400 uppercase tracking-wider">
                        <th class="px-5 py-3">Artikel</th>
                        <th class="px-5 py-3">Slug</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/60 text-sm">
                    @forelse($articles as $article)
                    <tr class="hover:bg-dark-700/20">
                        <td class="px-5 py-4">
                            <div class="flex items-start gap-3">
                                @if($article->image)
                                    <img src="{{ Storage::url($article->image) }}" class="w-14 h-14 object-cover rounded-lg border border-dark-600" alt="{{ $article->title }}">
                                @else
                                    <div class="w-14 h-14 rounded-lg border border-dark-600 bg-dark-900 text-gray-500 flex items-center justify-center">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold text-white">{{ $article->title }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($article->content), 90) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-xs font-mono text-gray-300">/{{ $article->slug }}</td>
                        <td class="px-5 py-4">
                            @if($article->is_published)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Published</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Draft</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-400">{{ $article->created_at->format('d M Y H:i') }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                @if($article->is_published)
                                    <a href="{{ route('front.article.show', $article->slug) }}" target="_blank" class="text-cyan-400 hover:text-cyan-300" title="Lihat di Front">
                                        <i class="fas fa-up-right-from-square"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.articles.edit', $article->id) }}" class="text-brand-400 hover:text-brand-300" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus artikel ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-gray-400">Belum ada artikel.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $articles->links() }}
    </div>
</div>
@endsection
