@extends('layouts.admin')

@section('title', 'Tambah OTP Provider')
@section('header', 'Tambah OTP Provider')

@section('content')
<div class="flex justify-center w-full">
    <div class="w-full max-w-3xl">
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('admin.otp-providers.index') }}" class="w-10 h-10 rounded-xl bg-dark-800 border border-dark-600 flex items-center justify-center text-gray-400 hover:text-white hover:bg-dark-700 transition-all">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-white tracking-tight">OTP Provider Baru</h1>
                <p class="text-sm text-gray-400 mt-1">Konfigurasi pengaturan provider pengiriman OTP.</p>
            </div>
        </div>

        <form id="otp-provider-form" action="{{ route('admin.otp-providers.store') }}" method="POST" class="bg-dark-800/40 backdrop-blur-xl p-8 rounded-2xl border border-dark-600/50 shadow-2xl space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Nama Provider <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Misal: Resend Email OTP">
                    @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Kode Driver <span class="text-rose-500">*</span></label>
                    <select name="code" id="driver-select" required class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                        <option value="fonnte">fonnte (WhatsApp)</option>
                        <option value="twilio">twilio (WhatsApp)</option>
                        <option value="smtp">smtp (Email)</option>
                        <option value="mailgun">mailgun (Email)</option>
                        <option value="sendgrid">sendgrid (Email)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Channel (Tipe) <span class="text-rose-500">*</span></label>
                <select name="type" id="type-select" required class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                    <option value="whatsapp">WhatsApp</option>
                    <option value="email">Email</option>
                </select>
            </div>

            <div class="bg-dark-800/30 p-5 rounded-xl border border-dark-700">
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider">KREDENSIAL API</label>
                    <button type="button" onclick="addCredentialRow()" class="text-xs bg-brand-500/10 text-brand-400 hover:bg-brand-500 hover:text-white px-3 py-1.5 rounded-lg transition">
                        <i class="fas fa-plus mr-1"></i> Tambah Baris
                    </button>
                </div>
                <div id="credentials-container"></div>
                <p class="text-xs text-gray-600 mt-2">Isi sesuai credential dari dashboard provider yang kamu pakai.</p>
            </div>

            <div class="flex gap-6 pt-2">
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-dark-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 border border-dark-600"></div>
                    <span class="ml-3 text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Aktivasi Provider</span>
                </label>
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" name="is_default" value="1" class="sr-only peer">
                    <div class="w-11 h-6 bg-dark-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500 border border-dark-600"></div>
                    <span class="ml-3 text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Jadikan Default</span>
                </label>
            </div>

            <div class="flex justify-end mt-8 pt-6 border-t border-dark-700/50">
                <button type="submit" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 transform transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Preset kredensial per driver
const DRIVER_PRESETS = {
    fonnte:   [{ key: 'token', value: '' }],
    twilio:   [{ key: 'sid', value: '' }, { key: 'token', value: '' }, { key: 'from', value: '' }],
    smtp:     [
        { key: 'host', value: '' }, 
        { key: 'port', value: '' }, 
        { key: 'username', value: '' }, 
        { key: 'password', value: '' }, 
        { key: 'encryption', value: '' },
        { key: 'from_address', value: '' },
        { key: 'from_name', value: '' }
    ],
    mailgun:  [{ key: 'domain', value: '' }, { key: 'secret', value: '' }],
    sendgrid: [{ key: 'api_key', value: '' }],
};

const DRIVER_TYPE = {
    fonnte: 'whatsapp', twilio: 'whatsapp',
    smtp: 'email', mailgun: 'email', sendgrid: 'email',
};

function addCredentialRow(key = '', value = '') {
    const container = document.getElementById('credentials-container');
    const row = document.createElement('div');
    row.className = 'flex items-center gap-3 mb-3';

    const keyInput = document.createElement('input');
    keyInput.type = 'text';
    keyInput.className = 'w-1/3 bg-dark-900 border border-dark-600 text-white text-xs rounded-lg p-2 font-mono';
    keyInput.placeholder = 'Key (misal: host)';
    keyInput.value = key;

    const valInput = document.createElement('input');
    valInput.type = 'text';
    valInput.className = 'flex-1 bg-dark-900 border border-dark-600 text-white text-xs rounded-lg p-2 font-mono';
    valInput.placeholder = 'Value rahasia...';
    valInput.value = value;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'flex-shrink-0 w-8 h-8 flex items-center justify-center text-rose-500 hover:text-white hover:bg-rose-500/20 rounded-lg transition';
    btn.innerHTML = '<i class="fas fa-times"></i>';
    btn.onclick = function() {
        row.remove();
    };

    row.appendChild(keyInput);
    row.appendChild(valInput);
    row.appendChild(btn);
    container.appendChild(row);
}

function loadPreset(driver) {
    const container = document.getElementById('credentials-container');
    container.innerHTML = '';
    const preset = DRIVER_PRESETS[driver] || [];
    preset.forEach(cred => addCredentialRow(cred.key, cred.value));

    // Auto set type
    const typeSelect = document.getElementById('type-select');
    if (DRIVER_TYPE[driver]) typeSelect.value = DRIVER_TYPE[driver];
}

// Inject credentials ke form sebelum submit
document.getElementById('otp-provider-form').addEventListener('submit', function () {
    const container = document.getElementById('credentials-container');
    const rows = container.querySelectorAll('div.flex');
    rows.forEach(row => {
        const inputs = row.querySelectorAll('input');
        const k = inputs[0].value.trim();
        const v = inputs[1].value.trim();
        if (k) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'credentials[' + k + ']';
            hidden.value = v;
            document.getElementById('otp-provider-form').appendChild(hidden);
        }
    });
});

// Init
document.getElementById('driver-select').addEventListener('change', function() {
    loadPreset(this.value);
});
loadPreset('fonnte');
</script>
@endpush
@endsection
