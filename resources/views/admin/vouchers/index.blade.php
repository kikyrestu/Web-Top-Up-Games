@extends('layouts.admin')
@section('title', 'Voucher & Promo')
@section('header', 'Manajemen Voucher')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <p class="text-gray-400 text-sm mt-1">Total: <span class="text-white font-bold">{{ $vouchers->total() }}</span> voucher</p>
    </div>
    <a href="{{ route('admin.vouchers.create') }}" class="bg-gradient-to-r from-[#f97316] to-[#ea580c] text-white font-bold py-2.5 px-5 rounded-xl text-sm hover:-translate-y-0.5 transform transition flex items-center gap-2">
        <i class="fas fa-plus"></i> Buat Voucher
    </a>
</div>

<div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl overflow-x-auto">
    <table class="min-w-full divide-y divide-dark-600 text-sm">
        <thead class="bg-dark-900/50">
            <tr>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Kode</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Diskon</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Min. Beli</th>
                <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Pemakaian</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase">Expired</th>
                <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-dark-700">
            @forelse($vouchers as $v)
            <tr class="hover:bg-dark-700/30 transition">
                <td class="px-4 py-3 font-mono text-[#f97316] font-black text-sm">{{ $v->code }}</td>
                <td class="px-4 py-3 text-white font-bold">
                    @if($v->type === 'percentage')
                        {{ (int)$v->value }}%
                        @if($v->max_discount) <span class="text-gray-500 text-xs">(max Rp {{ number_format($v->max_discount, 0, ',', '.') }})</span> @endif
                    @else
                        Rp {{ number_format($v->value, 0, ',', '.') }}
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-300">{{ $v->min_purchase > 0 ? 'Rp ' . number_format($v->min_purchase, 0, ',', '.') : '-' }}</td>
                <td class="px-4 py-3 text-center text-gray-300">
                    {{ $v->uses_count }} / {{ $v->max_uses ?? '∞' }}
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $v->expires_at ? $v->expires_at->format('d M Y') : '∞' }}</td>
                <td class="px-4 py-3 text-center">
                    @if($v->is_active && (!$v->expires_at || $v->expires_at->isFuture()))
                        <span class="text-emerald-400 text-xs font-bold">● Aktif</span>
                    @else
                        <span class="text-red-400 text-xs font-bold">● Nonaktif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.vouchers.edit', $v) }}" class="text-[#f97316] hover:text-white text-xs font-bold mr-3 transition"><i class="fas fa-edit mr-1"></i>Edit</a>
                    <form action="{{ route('admin.vouchers.destroy', $v) }}" method="POST" class="inline" onsubmit="return confirm('Hapus voucher ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-300 text-xs font-bold transition"><i class="fas fa-trash mr-1"></i>Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-12 text-center text-gray-500 italic">Belum ada voucher. <a href="{{ route('admin.vouchers.create') }}" class="text-[#f97316]">Buat sekarang →</a></td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $vouchers->links() }}</div>
@endsection
