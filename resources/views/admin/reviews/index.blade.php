@extends('layouts.admin')
@section('title', 'Moderasi Review')
@section('header', 'Ulasan Produk')

@section('content')
<div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 overflow-x-auto">
    <table class="min-w-full divide-y divide-dark-600 text-sm">
        <thead class="bg-dark-900/50">
            <tr>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">User</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Produk</th>
                <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Rating</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Komentar</th>
                <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Status</th>
                <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-dark-700">
            @forelse($reviews as $r)
            <tr class="hover:bg-dark-700/30 transition {{ $r->is_approved ? '' : 'bg-amber-900/5' }}">
                <td class="px-4 py-3 text-gray-300 text-xs whitespace-nowrap">{{ $r->user->name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-300 text-xs">{{ Str::limit($r->product->name ?? '-', 30) }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="text-amber-400 text-sm">{{ str_repeat('★', $r->rating) }}<span class="text-gray-600">{{ str_repeat('☆', 5 - $r->rating) }}</span></span>
                </td>
                <td class="px-4 py-3 text-gray-400 text-xs">{{ Str::limit($r->comment, 60) ?? '-' }}</td>
                <td class="px-4 py-3 text-center">
                    @if($r->is_approved)
                        <span class="text-emerald-400 text-xs font-bold">● Publik</span>
                    @else
                        <span class="text-amber-500 text-xs font-bold">● Pending</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <div class="flex items-center justify-center gap-2">
                    @if(!$r->is_approved)
                        <form action="{{ route('admin.reviews.approve', $r) }}" method="POST" class="inline">
                            @csrf @method('PATCH')
                            <button class="text-xs font-bold text-emerald-400 hover:text-emerald-300 transition">✓ Setujui</button>
                        </form>
                    @else
                        <form action="{{ route('admin.reviews.reject', $r) }}" method="POST" class="inline">
                            @csrf @method('PATCH')
                            <button class="text-xs font-bold text-amber-400 hover:text-amber-300 transition">⊘ Tolak</button>
                        </form>
                    @endif
                    <form action="{{ route('admin.reviews.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('Hapus review ini?')">
                        @csrf @method('DELETE')
                        <button class="text-xs font-bold text-red-400 hover:text-red-300 transition ml-2">✕ Hapus</button>
                    </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500 italic">Belum ada ulasan produk.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $reviews->links() }}</div>
@endsection
