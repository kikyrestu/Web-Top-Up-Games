@extends('layouts.admin')

@section('title', 'API Provider')
@section('header', 'API Provider Configuration')

@section('content')
@php
    $providerPresets = [
        [
            'provider' => 'rajabiller',
            'code' => 'rajabiller',
            'name' => 'Rajabiller',
            'credentials' => [
                ['key' => 'uid', 'label' => 'UID / User ID Outlet', 'placeholder' => 'Contoh: SP300203', 'type' => 'text', 'hint' => 'User ID outlet dari dashboard Rajabiller'],
                ['key' => 'pin', 'label' => 'PIN Transaksi', 'placeholder' => 'PIN numerik', 'type' => 'password', 'hint' => 'Kosongkan jika tidak ingin mengubah'],
                ['key' => 'env', 'label' => 'Environment', 'placeholder' => '', 'type' => 'select', 'options' => [
                    'production' => '🟢 Production – api.rajabiller.com',
                    'sandbox'    => '🟡 Sandbox / Dev – c-dev-api.rajabiller.com',
                ]],
            ],
        ],
        [
            'provider' => 'digiflazz',
            'code' => 'digiflazz',
            'name' => 'Digiflazz',
            'credentials' => [
                ['key' => 'username', 'label' => 'Username', 'placeholder' => 'username_digiflazz', 'type' => 'text'],
                ['key' => 'api_key', 'label' => 'API Key', 'placeholder' => 'api_key_digiflazz', 'type' => 'text'],
                ['key' => 'url', 'label' => 'Custom Base URL (Opsional)', 'placeholder' => 'https://api.digiflazz.com/v1/', 'type' => 'text'],
            ],
        ],
        [
            'provider' => 'orderkuota',
            'code' => 'orderkuota',
            'name' => 'OrderKuota (OkeConnect H2H)',
            'credentials' => [
                ['key' => 'member_id', 'label' => 'Member ID', 'placeholder' => 'OK00123', 'type' => 'text'],
                ['key' => 'pin', 'label' => 'PIN Transaksi (4 digit)', 'placeholder' => '1234', 'type' => 'password'],
                ['key' => 'password', 'label' => 'Password H2H', 'placeholder' => 'password_h2h', 'type' => 'password'],
            ],
        ],
    ];

    $providersByCode = [];
    foreach ($providers as $provider) {
        $codeKey = strtolower((string) $provider->code);
        if (!isset($providersByCode[$codeKey])) {
            $providersByCode[$codeKey] = $provider;
        }
    }
@endphp

<div class="space-y-5">
    <div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl p-5">
        <h1 class="text-2xl font-black text-white tracking-tight">API Provider Setup</h1>
        <p class="text-sm text-gray-400 mt-1">Semua konfigurasi provider tersedia langsung di halaman ini tanpa create/edit terpisah.</p>

        @if($errors->any())
            <div class="mt-3 bg-red-900/20 border border-red-700/40 rounded p-3 text-xs text-red-200">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @foreach($providerPresets as $preset)
        @php
            $provider = $providersByCode[$preset['code']] ?? null;
            $existingCredentials = $provider?->credentials ?? [];
            unset($existingCredentials['__hashes']);
        @endphp

        <div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl p-6">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-black text-white tracking-tight">{{ $preset['name'] }}</h3>
                    <p class="text-xs text-gray-500 uppercase font-mono">{{ $preset['code'] }}</p>
                </div>
                @if($provider)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $provider->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-gray-500/10 text-gray-300 border border-gray-500/20' }}">
                        {{ $provider->is_active ? 'Aktif' : 'Tersimpan' }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Belum disetup</span>
                @endif
            </div>

            <form action="{{ $provider ? route('admin.api-providers.update', $provider->id) : route('admin.api-providers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5 provider-inline-form">
                @csrf
                @if($provider)
                    @method('PUT')
                @endif

                <input type="hidden" name="provider" value="{{ $preset['provider'] }}">
                <input type="hidden" name="current_password" value="">
                <input type="hidden" name="security_pin" value="">
                <input type="hidden" name="new_pin" value="">
                <input type="hidden" name="new_pin_confirmation" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Nama Provider</label>
                        <input type="text" name="name" value="{{ old('name', $provider->name ?? $preset['name']) }}" required class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Kode Provider</label>
                        <input type="text" name="code" value="{{ old('code', $provider->code ?? $preset['code']) }}" required class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 lowercase font-mono">
                    </div>
                </div>

                <div class="bg-dark-800/30 p-5 rounded-xl border border-dark-700">
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Kredensial API (Terenkripsi)</label>
                    <p class="text-[11px] text-gray-500 mb-3">Isi hanya nilai yang ingin diubah. Nilai lama akan dimasking sebagai *****.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($preset['credentials'] as $field)
                            @php $fieldType = $field['type'] ?? 'text'; @endphp
                            <div class="{{ $loop->last && count($preset['credentials']) % 2 === 1 ? 'md:col-span-2' : '' }}">
                                <label class="block text-gray-400 text-xs mb-1 font-bold">{{ $field['label'] }}</label>

                                @if($fieldType === 'select')
                                    <select name="credentials[{{ $field['key'] }}]" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                                        @foreach($field['options'] as $optVal => $optLabel)
                                            <option value="{{ $optVal }}" {{ (($existingCredentials[$field['key']] ?? 'production') === $optVal) ? 'selected' : '' }}>{{ $optLabel }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        type="{{ $fieldType }}"
                                        name="credentials[{{ $field['key'] }}]"
                                        value="{{ ($fieldType !== 'password' && array_key_exists($field['key'], $existingCredentials)) ? '*****' : '' }}"
                                        placeholder="{{ array_key_exists($field['key'], $existingCredentials) ? ($fieldType === 'password' ? 'Isi untuk ganti' : '*****') : $field['placeholder'] }}"
                                        class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs"
                                    >
                                @endif

                                @if(!empty($field['hint']))
                                    <p class="text-gray-600 text-[10px] mt-1">{{ $field['hint'] }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-dark-800/30 p-3 rounded-xl border border-dark-700 text-xs text-gray-300">
                    Verifikasi keamanan akan tampil di popup saat credential diubah.
                    @if($pinSecurityStatus['has_api_pin'])
                        @if($pinSecurityStatus['is_locked'])
                            <span class="ml-2 text-red-300">PIN terkunci {{ (int) ceil($pinSecurityStatus['lock_seconds'] / 60) }} menit.</span>
                        @else
                            <span class="ml-2 text-blue-300">Sisa percobaan PIN: {{ $pinSecurityStatus['attempts_remaining'] }} / {{ $pinSecurityStatus['max_attempts'] }}.</span>
                        @endif
                    @else
                        <span class="ml-2 text-amber-300">PIN belum dibuat, popup akan minta pembuatan PIN baru.</span>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Logo Provider</label>
                        <input type="file" name="logo" accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 block bg-dark-900 border border-dark-600 rounded-xl p-2 focus:outline-none">
                        @if($provider?->logo)
                            <img src="{{ asset('storage/' . $provider->logo) }}" class="w-14 h-14 object-cover rounded-lg mt-2 border border-dark-600" alt="Logo {{ $preset['name'] }}">
                        @endif
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Deskripsi</label>
                        <textarea name="description" rows="3" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">{{ old('description', $provider->description ?? '') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-dark-700/60">
                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer group">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $provider->is_active ?? true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-dark-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500 border border-dark-600"></div>
                            <span class="ml-3 text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Aktifkan Provider</span>
                        </label>

                        @if($provider)
                            <button type="button"
                                onclick="testApiProvider({{ $provider->id }}, this)"
                                class="inline-flex items-center gap-2 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 font-bold py-2 px-4 rounded-xl border border-blue-500/30 transition-all text-sm">
                                <i class="fas fa-plug"></i> Test Koneksi
                            </button>
                        @endif
                    </div>

                    <button type="button" class="open-security-modal bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 transform transition-all">
                        {{ $provider ? 'Simpan Perubahan' : 'Simpan Provider' }}
                    </button>
                </div>

                @if($provider)
                    <div id="test-result-provider-{{ $provider->id }}" class="hidden mt-3 p-3 rounded-xl text-sm font-medium"></div>
                @endif
            </form>
        </div>
    @endforeach
</div>

@include('admin.partials.inline_security_modal', [
    'namespace' => 'provider',
    'formSelector' => '.provider-inline-form',
    'buttonSelector' => '.provider-inline-form .open-security-modal',
    'hasApiPin' => $hasApiPin,
    'pinSecurityStatus' => $pinSecurityStatus,
    'panelClass' => 'bg-dark-900 border-dark-600',
    'inputClass' => 'bg-dark-800 border-dark-600 rounded-xl text-white',
    'cancelButtonClass' => 'bg-dark-700 hover:bg-dark-600',
    'confirmButtonClass' => 'bg-brand-500 hover:bg-brand-400',
])

<script>
function testApiProvider(id, btn) {
    const resultDiv = document.getElementById('test-result-provider-' + id);
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    resultDiv.classList.remove('hidden', 'bg-emerald-500/10', 'text-emerald-400', 'border-emerald-500/20', 'bg-red-500/10', 'text-red-400', 'border-red-500/20');

    fetch('/admin/api-providers/' + id + '/test-connection', {
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
            resultDiv.className = 'mt-3 p-3 rounded-xl text-sm font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
            resultDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>' + data.message;
        } else {
            resultDiv.className = 'mt-3 p-3 rounded-xl text-sm font-medium bg-red-500/10 text-red-400 border border-red-500/20';
            resultDiv.innerHTML = '<i class="fas fa-times-circle mr-2"></i>' + data.message;
        }
    })
    .catch(err => {
        resultDiv.classList.remove('hidden');
        resultDiv.className = 'mt-3 p-3 rounded-xl text-sm font-medium bg-red-500/10 text-red-400 border border-red-500/20';
        resultDiv.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Gagal menghubungi server: ' + err.message;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}
</script>
@endsection