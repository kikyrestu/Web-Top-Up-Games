@extends('layouts.admin')

@section('title', 'OTP Provider')
@section('header', 'Manajemen OTP Provider')

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl p-5">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">OTP Provider</h1>
            <p class="text-sm text-gray-400 mt-1">Kelola provider untuk pengiriman OTP (WhatsApp, Email).</p>
        </div>
        <a href="{{ route('admin.otp-providers.create') }}" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2 px-5 rounded-xl shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 transform transition-all flex items-center gap-2">
            <i class="fas fa-plus"></i> Tambah Provider
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4 text-sm text-emerald-400 font-bold flex items-center gap-3">
            <i class="fas fa-check-circle text-lg"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-400">
                <thead class="text-xs text-gray-300 uppercase bg-dark-900/50 border-b border-dark-600/50">
                    <tr>
                        <th class="px-6 py-4 font-bold">Provider</th>
                        <th class="px-6 py-4 font-bold">Tipe</th>
                        <th class="px-6 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 font-bold">Default</th>
                        <th class="px-6 py-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-600/50">
                    @forelse($providers as $provider)
                        <tr class="hover:bg-dark-700/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $provider->name }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $provider->code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="uppercase font-bold text-xs">{{ $provider->type }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($provider->is_active)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-500/10 text-gray-300 border border-gray-500/20">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($provider->is_default)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20"><i class="fas fa-star mr-1"></i> Default</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button type="button" onclick="testConnection({{ $provider->id }}, this)" class="inline-flex items-center gap-1.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 font-bold py-1.5 px-3 rounded-lg border border-blue-500/30 transition-all text-xs">
                                    <i class="fas fa-plug"></i> Test
                                </button>
                                <a href="{{ route('admin.otp-providers.edit', $provider->id) }}" class="inline-flex items-center gap-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 font-bold py-1.5 px-3 rounded-lg border border-amber-500/20 transition-all text-xs">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.otp-providers.destroy', $provider->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus provider ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 font-bold py-1.5 px-3 rounded-lg border border-rose-500/20 transition-all text-xs">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                                <div id="test-result-{{ $provider->id }}" class="hidden mt-2 p-2 rounded-lg text-xs font-medium text-left"></div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">Belum ada OTP Provider yang ditambahkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($providers->hasPages())
            <div class="px-6 py-4 border-t border-dark-600/50">
                {{ $providers->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function testConnection(id, btn) {
    const resultDiv = document.getElementById('test-result-' + id);
    const originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    resultDiv.classList.remove('hidden', 'bg-emerald-500/10', 'text-emerald-400', 'border-emerald-500/20', 'bg-rose-500/10', 'text-rose-400', 'border-rose-500/20');
    
    // We expect the correct route for testing. Assuming the route naming uses singular pattern or matching default controller action
    fetch('/admin/otp-providers/' + id + '/test', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        resultDiv.classList.remove('hidden');
        if (data.success) {
            resultDiv.className = 'mt-2 p-2 rounded-lg text-xs font-medium text-left bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
            resultDiv.innerHTML = '<i class="fas fa-check-circle mr-1"></i> ' + data.message;
        } else {
            resultDiv.className = 'mt-2 p-2 rounded-lg text-xs font-medium text-left bg-rose-500/10 text-rose-400 border border-rose-500/20';
            resultDiv.innerHTML = '<i class="fas fa-times-circle mr-1"></i> ' + data.message;
        }
    })
    .catch(err => {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'mt-2 p-2 rounded-lg text-xs font-medium text-left bg-rose-500/10 text-rose-400 border border-rose-500/20';
        resultDiv.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Gagal menghubungi server: ' + err.message;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>
@endsection
