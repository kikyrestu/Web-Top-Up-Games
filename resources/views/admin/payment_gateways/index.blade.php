@extends('layouts.admin')

@section('title', 'Payment Gateway')
@section('header', 'Payment Gateway')

@section('content')
@php
    $gatewayPresets = [
        [
            'provider' => 'midtrans',
            'code' => 'midtrans',
            'name' => 'Midtrans',
            'credentials' => [
                ['key' => 'server_key', 'label' => 'Server Key', 'placeholder' => 'SB-Mid-server-xxxx'],
            ],
        ],
        [
            'provider' => 'klikqris',
            'code' => 'klikqris',
            'name' => 'KlikQRIS',
            'credentials' => [
                ['key' => 'api_key', 'label' => 'API Key', 'placeholder' => 'Paste API Key KlikQRIS'],
                ['key' => 'merchant_id', 'label' => 'Merchant ID', 'placeholder' => 'Paste Merchant ID'],
            ],
        ],
        [
            'provider' => 'doku',
            'code' => 'doku',
            'name' => 'DompetX / DOKU',
            'credentials' => [
                ['key' => 'client_id', 'label' => 'Client ID', 'placeholder' => 'Paste Client ID DOKU'],
                ['key' => 'secret_key', 'label' => 'Secret Key', 'placeholder' => 'Paste Secret Key DOKU'],
            ],
        ],
    ];

    $gatewaysByCode = [];
    foreach ($gateways as $gateway) {
        $codeKey = strtolower((string) $gateway->code);
        if (!isset($gatewaysByCode[$codeKey])) {
            $gatewaysByCode[$codeKey] = $gateway;
        }
    }
@endphp

<div class="space-y-5">
    <div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl p-5">
        <h2 class="text-2xl font-black text-white tracking-tight">Payment Gateway Setup</h2>
        <p class="text-xs text-gray-400 mt-1">Konfigurasi langsung di halaman ini. Tidak perlu buka halaman tambah/edit terpisah.</p>

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

    @foreach($gatewayPresets as $preset)
        @php
            $gateway = $gatewaysByCode[$preset['code']] ?? null;
            $existingCredentials = $gateway?->credentials ?? [];
            unset($existingCredentials['__hashes']);
        @endphp

        <div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl p-6">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-black text-white tracking-tight">{{ $preset['name'] }}</h3>
                    <p class="text-xs text-gray-500 uppercase font-mono">{{ $preset['code'] }}</p>
                </div>
                @if($gateway)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $gateway->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-gray-500/10 text-gray-300 border border-gray-500/20' }}">
                        {{ $gateway->is_active ? 'Aktif' : 'Tersimpan' }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Belum disetup</span>
                @endif
            </div>

            <form action="{{ $gateway ? route('admin.payment-gateways.update', $gateway->id) : route('admin.payment-gateways.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 gateway-inline-form">
                @csrf
                @if($gateway)
                    @method('PUT')
                @endif

                <input type="hidden" name="provider" value="{{ $preset['provider'] }}">
                <input type="hidden" name="current_password" value="">
                <input type="hidden" name="security_pin" value="">
                <input type="hidden" name="new_pin" value="">
                <input type="hidden" name="new_pin_confirmation" value="">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Nama Gateway</label>
                        <input type="text" name="name" value="{{ old('name', $gateway->name ?? $preset['name']) }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" required>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Kode Gateway</label>
                        <input type="text" name="code" value="{{ old('code', $gateway->code ?? $preset['code']) }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 lowercase font-mono" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Biaya Admin Flat (Rp)</label>
                        <input type="number" name="fee_flat" value="{{ old('fee_flat', $gateway->fee_flat ?? 0) }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Biaya Admin Persen (%)</label>
                        <input type="number" step="0.01" name="fee_percent" value="{{ old('fee_percent', $gateway->fee_percent ?? 0) }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                    </div>
                </div>

                <div class="bg-dark-800/30 p-5 rounded-xl border border-dark-700">
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Kredensial API (Terenkripsi)</label>
                    <p class="text-xs text-gray-400 mb-3">Jika sudah pernah disimpan, nilai lama ditampilkan sebagai *****.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($preset['credentials'] as $field)
                            <div class="{{ count($preset['credentials']) === 1 ? 'md:col-span-2' : '' }}">
                                <label class="block text-gray-400 text-xs mb-1">{{ $field['label'] }}</label>
                                <input
                                    type="text"
                                    name="credentials[{{ $field['key'] }}]"
                                    value="{{ array_key_exists($field['key'], $existingCredentials) ? '*****' : '' }}"
                                    class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs"
                                    placeholder="{{ array_key_exists($field['key'], $existingCredentials) ? '*****' : $field['placeholder'] }}"
                                >
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-dark-800/30 p-3 rounded-xl border border-dark-700 text-xs text-gray-300">
                    Verifikasi keamanan akan muncul dalam popup saat credential diubah.
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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Logo Gateway</label>
                        <input type="file" name="logo" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 block bg-dark-900 border border-dark-600 rounded-xl p-2 focus:outline-none" accept="image/*">
                        @if($gateway?->logo)
                            <img src="{{ asset('storage/' . $gateway->logo) }}" class="w-14 h-14 object-cover rounded-lg mt-2 border border-dark-600" alt="Logo {{ $preset['name'] }}">
                        @endif
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer group pb-2">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $gateway->is_active ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-dark-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500 border border-dark-600"></div>
                        <span class="ml-3 text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Aktifkan</span>
                    </label>

                    <label class="relative inline-flex items-center cursor-pointer group pb-2">
                        <input type="checkbox" name="is_test_mode" value="1" class="sr-only peer" {{ old('is_test_mode', $gateway->is_test_mode ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-dark-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500 border border-dark-600"></div>
                        <span class="ml-3 text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Sandbox/Test</span>
                    </label>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-dark-700/60">
                    <div class="flex items-center gap-3">
                        @if($gateway)
                            <span class="text-xs text-gray-500">Update konfigurasi lalu simpan.</span>
                            <button type="button"
                                onclick="testPaymentGateway({{ $gateway->id }}, this)"
                                class="inline-flex items-center gap-2 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 font-bold py-2 px-4 rounded-xl border border-blue-500/30 transition-all text-sm">
                                <i class="fas fa-plug"></i> Test Koneksi
                            </button>
                        @else
                            <span class="text-xs text-gray-500">Simpan untuk membuat gateway ini.</span>
                        @endif
                    </div>

                    <button type="button" class="open-security-modal bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 transform transition-all">
                        {{ $gateway ? 'Simpan Perubahan' : 'Simpan Gateway' }}
                    </button>
                </div>

                @if($gateway)
                    <div id="test-result-gateway-{{ $gateway->id }}" class="hidden mt-3 p-3 rounded-xl text-sm font-medium"></div>
                @endif
            </form>
        </div>
    @endforeach
</div>

@include('admin.partials.inline_security_modal', [
    'namespace' => 'payment',
    'formSelector' => '.gateway-inline-form',
    'buttonSelector' => '.gateway-inline-form .open-security-modal',
    'hasApiPin' => $hasApiPin,
    'pinSecurityStatus' => $pinSecurityStatus,
    'panelClass' => 'bg-dark-900 border-dark-600',
    'inputClass' => 'bg-dark-800 border-dark-600 rounded-xl text-white',
    'cancelButtonClass' => 'bg-dark-700 hover:bg-dark-600',
    'confirmButtonClass' => 'bg-brand-500 hover:bg-brand-400',
])

<script>
function testPaymentGateway(id, btn) {
    const resultDiv = document.getElementById('test-result-gateway-' + id);
    const originalHtml = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    resultDiv.classList.remove('hidden', 'bg-emerald-500/10', 'text-emerald-400', 'border-emerald-500/20', 'bg-red-500/10', 'text-red-400', 'border-red-500/20');

    fetch('/admin/payment-gateways/' + id + '/test-connection', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        resultDiv.classList.remove('hidden');
        let htmlContent = '';
        if (data.success) {
            resultDiv.className = 'mt-3 p-3 rounded-xl text-sm font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
            htmlContent = '<i class="fas fa-check-circle mr-2"></i>' + data.message;
        } else {
            resultDiv.className = 'mt-3 p-3 rounded-xl text-sm font-medium bg-red-500/10 text-red-400 border border-red-500/20';
            htmlContent = '<i class="fas fa-times-circle mr-2"></i>' + data.message;
        }
        
        if (data.raw) {
            htmlContent += '<div class="mt-2 p-2 bg-dark-900 rounded border border-dark-600 text-left overflow-x-auto"><pre class="text-xs text-gray-300"><code>' + JSON.stringify(data.raw, null, 2) + '</code></pre></div>';
        }
        resultDiv.innerHTML = htmlContent;
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