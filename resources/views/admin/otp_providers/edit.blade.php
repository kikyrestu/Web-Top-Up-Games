@extends('layouts.admin')

@section('title', 'Edit OTP Provider')
@section('header', 'Edit OTP Provider')

@section('content')
<div class="flex justify-center w-full">
    <div class="w-full max-w-3xl">
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('admin.otp-providers.index') }}" class="w-10 h-10 rounded-xl bg-dark-800 border border-dark-600 flex items-center justify-center text-gray-400 hover:text-white hover:bg-dark-700 transition-all">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">Edit OTP Provider</h1>
                <p class="text-sm text-gray-400 mt-1">Ubah konfigurasi pengaturan provider pengiriman OTP.</p>
            </div>
        </div>

        <form id="otp-edit-form" action="{{ route('admin.otp-providers.update', $otpProvider->id) }}" method="POST" class="bg-dark-800/40 backdrop-blur-xl p-8 rounded-2xl border border-dark-600/50 shadow-2xl space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Nama Provider <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $otpProvider->name) }}" required class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                    @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Kode Driver</label>
                    <input type="text" value="{{ strtoupper($otpProvider->code) }}" readonly class="w-full bg-dark-900 border border-dark-600 text-gray-400 text-sm rounded-xl p-3 cursor-not-allowed">
                    <input type="hidden" name="code" value="{{ $otpProvider->code }}">
                    <p class="text-[11px] text-gray-500 mt-1">Kode driver tidak bisa diubah.</p>
                </div>
            </div>

            <div>
                <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Channel (Tipe)</label>
                <input type="text" value="{{ strtoupper($otpProvider->type) }}" readonly class="w-full bg-dark-900 border border-dark-600 text-gray-400 text-sm rounded-xl p-3 cursor-not-allowed">
                <input type="hidden" name="type" value="{{ $otpProvider->type }}">
            </div>

            <div class="bg-dark-800/30 p-5 rounded-xl border border-dark-700">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider">KREDENSIAL API</label>
                        <p class="text-[11px] text-gray-500 mt-1">Kosongkan value yang tidak ingin diubah.</p>
                    </div>
                    <button type="button" onclick="addEditCredRow()" class="text-xs bg-brand-500/10 text-brand-400 hover:bg-brand-500 hover:text-white px-3 py-1.5 rounded-lg transition">
                        <i class="fas fa-plus mr-1"></i> Tambah Baris
                    </button>
                </div>

                {{-- Rendered directly from PHP -- no JS timing issues --}}
                <div id="edit-credentials-container">
                    @foreach(($otpProvider->credentials ?? []) as $credKey => $credVal)
                    <div class="cred-row flex items-center gap-3 mb-3">
                        <input type="text"
                               value="{{ $credKey }}"
                               readonly
                               class="w-1/3 bg-dark-900 border border-dark-600 text-gray-400 text-xs rounded-lg p-2 font-mono cursor-not-allowed"
                               data-cred-key="{{ $credKey }}">
                        <input type="text"
                               placeholder="(kosongkan jika tidak diubah)"
                               class="flex-1 bg-dark-900 border border-dark-600 text-white text-xs rounded-lg p-2 font-mono"
                               data-cred-val>
                        <button type="button"
                                onclick="this.closest('.cred-row').remove()"
                                class="flex-shrink-0 w-8 h-8 flex items-center justify-center text-rose-500 hover:text-white hover:bg-rose-500/20 rounded-lg transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-6 pt-2">
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $otpProvider->is_active) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-dark-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 border border-dark-600"></div>
                    <span class="ml-3 text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Aktivasi Provider</span>
                </label>
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" name="is_default" value="1" class="sr-only peer" {{ old('is_default', $otpProvider->is_default) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-dark-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500 border border-dark-600"></div>
                    <span class="ml-3 text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Jadikan Default (Per Tipe)</span>
                </label>
            </div>

            <div class="flex justify-end mt-8 pt-6 border-t border-dark-700/50">
                <button type="submit" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 transform transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Tambah baris credential baru (untuk menambah key baru)
function addEditCredRow() {
    const container = document.getElementById('edit-credentials-container');
    const row = document.createElement('div');
    row.className = 'cred-row flex items-center gap-3 mb-3';

    const keyInput = document.createElement('input');
    keyInput.type = 'text';
    keyInput.className = 'w-1/3 bg-dark-900 border border-dark-600 text-white text-xs rounded-lg p-2 font-mono';
    keyInput.placeholder = 'Key baru...';
    keyInput.setAttribute('data-cred-key', '');
    keyInput.addEventListener('input', function() {
        this.setAttribute('data-cred-key', this.value);
    });

    const valInput = document.createElement('input');
    valInput.type = 'text';
    valInput.className = 'flex-1 bg-dark-900 border border-dark-600 text-white text-xs rounded-lg p-2 font-mono';
    valInput.placeholder = 'Value...';
    valInput.setAttribute('data-cred-val', '');

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'flex-shrink-0 w-8 h-8 flex items-center justify-center text-rose-500 hover:text-white hover:bg-rose-500/20 rounded-lg transition';
    btn.innerHTML = '<i class="fas fa-times"></i>';
    btn.onclick = function() { row.remove(); };

    row.appendChild(keyInput);
    row.appendChild(valInput);
    row.appendChild(btn);
    container.appendChild(row);
}

// Inject hidden inputs sebelum submit
document.getElementById('otp-edit-form').addEventListener('submit', function() {
    document.querySelectorAll('#edit-credentials-container .cred-row').forEach(function(row) {
        const keyEl = row.querySelector('[data-cred-key]');
        const valEl = row.querySelector('[data-cred-val]');
        const k = keyEl ? keyEl.getAttribute('data-cred-key') || keyEl.value.trim() : '';
        const v = valEl ? valEl.value.trim() : '';
        if (k) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'credentials[' + k + ']';
            hidden.value = v;
            document.getElementById('otp-edit-form').appendChild(hidden);
        }
    });
});
</script>
@endpush
@endsection
