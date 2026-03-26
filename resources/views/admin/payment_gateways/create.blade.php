@extends('layouts.admin')

@section('title', 'Tambah Gateway')
@section('header', 'Tambah Payment Gateway Baru')

@section('content')
<div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 max-w-4xl" x-data="gatewayForm({{ $hasApiPin ? 'true' : 'false' }})">
    <form action="{{ route('admin.payment-gateways.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-300 text-sm font-bold mb-2">Provider</label>
                <select name="provider" x-model="provider" @change="syncDefaults" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none">
                    <option value="midtrans">Midtrans</option>
                    <option value="klikqris">KlikQRIS</option>
                    <option value="doku">DompetX / DOKU</option>
                </select>
            </div>
            <div>
                <label class="block text-gray-300 text-sm font-bold mb-2">Kode Gateway</label>
                <input type="text" name="code" x-model="code" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none lowercase" required>
                <p class="text-xs text-gray-400 mt-1">Gunakan kode default agar service auto-terhubung: midtrans / klikqris / doku.</p>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-300 text-sm font-bold mb-2">Nama Gateway (Label Admin)</label>
            <input type="text" name="name" x-model="name" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-300 text-sm font-bold mb-2">Biaya Admin Flat (Rp)</label>
                <input type="number" name="fee_flat" value="0" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none">
            </div>
            <div>
                <label class="block text-gray-300 text-sm font-bold mb-2">Biaya Admin Persen (%)</label>
                <input type="number" step="0.01" name="fee_percent" value="0" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none">
            </div>
        </div>

        <div class="mb-4 bg-gray-900/50 p-4 rounded border">
            <label class="block text-gray-300 text-sm font-bold mb-2">Kredensial API (Aman & Terenkripsi)</label>
            <p class="text-xs text-gray-400 mb-3">Paste credential sesuai provider. Nilai sensitif akan disimpan terenkripsi dan ditampilkan sebagai ***** setelah disimpan.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" x-show="provider === 'midtrans'">
                <div class="md:col-span-2">
                    <label class="block text-gray-400 text-xs mb-1">Server Key</label>
                    <input type="text" name="credentials[server_key]" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded text-sm" placeholder="SB-Mid-server-xxxx">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" x-show="provider === 'klikqris'" style="display: none;">
                <div>
                    <label class="block text-gray-400 text-xs mb-1">API Key</label>
                    <input type="text" name="credentials[api_key]" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded text-sm" placeholder="Paste API Key KlikQRIS">
                </div>
                <div>
                    <label class="block text-gray-400 text-xs mb-1">Merchant ID</label>
                    <input type="text" name="credentials[merchant_id]" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded text-sm" placeholder="Paste Merchant ID">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3" x-show="provider === 'doku'" style="display: none;">
                <div>
                    <label class="block text-gray-400 text-xs mb-1">Client ID</label>
                    <input type="text" name="credentials[client_id]" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded text-sm" placeholder="Paste Client ID DOKU">
                </div>
                <div>
                    <label class="block text-gray-400 text-xs mb-1">Secret Key</label>
                    <input type="text" name="credentials[secret_key]" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded text-sm" placeholder="Paste Secret Key DOKU">
                </div>
            </div>

            @error('credentials')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4 bg-gray-900/50 p-4 rounded border">
            <label class="block text-gray-300 text-sm font-bold mb-2">Verifikasi Keamanan</label>
            <p class="text-xs text-gray-400 mb-3">Saat menyimpan/mengubah API key, wajib verifikasi password dan PIN admin.</p>

            @if($pinSecurityStatus['has_api_pin'])
                @if($pinSecurityStatus['is_locked'])
                    <div class="mb-3 text-xs bg-red-900/20 border border-red-700/40 text-red-300 rounded px-3 py-2">
                        PIN terkunci sementara. Coba lagi dalam {{ (int) ceil($pinSecurityStatus['lock_seconds'] / 60) }} menit.
                    </div>
                @else
                    <div class="mb-3 text-xs bg-blue-900/20 border border-blue-700/40 text-blue-300 rounded px-3 py-2">
                        Sisa percobaan PIN: <span class="font-bold">{{ $pinSecurityStatus['attempts_remaining'] }}</span> / {{ $pinSecurityStatus['max_attempts'] }}
                    </div>
                @endif
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-gray-400 text-xs mb-1">Password Admin</label>
                    <input type="password" name="current_password" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded text-sm" placeholder="Isi saat ubah credential">
                    @error('current_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <template x-if="hasApiPin">
                    <div>
                        <label class="block text-gray-400 text-xs mb-1">PIN Admin (6 digit)</label>
                        <input type="password" name="security_pin" inputmode="numeric" maxlength="6" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded text-sm" placeholder="******">
                        @error('security_pin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </template>
                <template x-if="!hasApiPin">
                    <div>
                        <div class="w-full px-3 py-2 bg-amber-900/20 border border-amber-700/40 text-amber-300 rounded text-xs">
                            PIN admin belum dibuat. Popup setup PIN akan muncul saat pertama kali isi API key.
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="mb-4 flex flex-col md:flex-row md:space-x-4">
            <div class="w-full md:w-1/3 mb-4 md:mb-0">
                <label class="block text-gray-300 text-sm font-bold mb-2">Logo Gateway</label>
                <input type="file" name="logo" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded" accept="image/*">
            </div>
            <div class="w-full md:w-2/3 flex space-x-6 items-end pb-2">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="form-checkbox h-5 w-5 text-blue-600" checked>
                    <span class="text-sm font-bold text-gray-300">Aktifkan Konfigurasi</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_test_mode" value="1" class="form-checkbox h-5 w-5 text-yellow-500" checked>
                    <span class="text-sm font-bold text-gray-300">Mode Sandbox/Test</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6 border-t pt-4">
            <a href="{{ route('admin.payment-gateways.index') }}" class="bg-gray-900/500 text-white font-bold py-2 px-4 rounded">Batal</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Simpan</button>
        </div>
    </form>

    <div x-show="showPinModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" style="display: none;">
        <div class="w-full max-w-md bg-gray-900 border border-gray-700 rounded-xl p-5 shadow-2xl">
            <h3 class="text-white font-bold text-lg mb-2">Buat PIN Admin Baru</h3>
            <p class="text-gray-400 text-xs mb-4">PIN ini dipakai untuk otorisasi setiap perubahan API key berikutnya.</p>

            <div class="space-y-3">
                <div>
                    <label class="block text-gray-400 text-xs mb-1">PIN Baru (6 digit)</label>
                    <input type="password" name="new_pin" inputmode="numeric" maxlength="6" class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded text-sm" placeholder="123456">
                    @error('new_pin')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-400 text-xs mb-1">Konfirmasi PIN</label>
                    <input type="password" name="new_pin_confirmation" inputmode="numeric" maxlength="6" class="w-full px-3 py-2 bg-gray-800 border border-gray-600 text-white rounded text-sm" placeholder="123456">
                </div>
                <p class="text-[11px] text-gray-400">Isi Password Admin pada bagian Verifikasi Keamanan di bawah form.</p>
            </div>

            <div class="mt-4 flex justify-end">
                <button type="button" @click="showPinModal = false" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function gatewayForm(hasApiPin) {
        return {
            hasApiPin,
            showPinModal: false,
            provider: 'midtrans',
            code: 'midtrans',
            name: 'Midtrans',
            init() {
                this.$nextTick(() => {
                    this.bindCredentialWatcher();
                });
            },
            syncDefaults() {
                if (this.provider === 'midtrans') {
                    this.code = 'midtrans';
                    this.name = 'Midtrans';
                } else if (this.provider === 'klikqris') {
                    this.code = 'klikqris';
                    this.name = 'KlikQRIS';
                } else {
                    this.code = 'doku';
                    this.name = 'DompetX / DOKU';
                }
            },
            bindCredentialWatcher() {
                if (this.hasApiPin) return;
                const inputs = this.$el.querySelectorAll('.credential-input, input[name^="credentials["]');
                inputs.forEach((input) => {
                    input.addEventListener('input', () => {
                        if (!this.showPinModal && input.value.trim() !== '') {
                            this.showPinModal = true;
                        }
                    });
                });
            }
        }
    }
</script>
@endpush
@endsection