@extends('layouts.admin')

@section('title', 'Edit API Provider')
@section('header', 'Edit API Provider')

@section('content')
<div class="flex justify-center w-full">
    <div class="w-full max-w-3xl">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.api-providers.index') }}" class="w-10 h-10 rounded-xl bg-dark-800 border border-dark-600 flex items-center justify-center text-gray-400 hover:text-white hover:bg-dark-700 transition-all">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight">Edit Provider: <span class="text-brand-400">{{ $apiProvider->name }}</span></h1>
                    <p class="text-sm text-gray-400 mt-1">Perbarui konfigurasi API provider dengan aman.</p>
                </div>
            </div>
            @if($apiProvider->is_active)
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 blur-[1px]"></span>Online</span>
            @else
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-2 blur-[1px]"></span>Offline</span>
            @endif
        </div>

        <form action="{{ route('admin.api-providers.update', $apiProvider->id) }}" method="POST" enctype="multipart/form-data" class="bg-dark-800/40 backdrop-blur-xl p-8 rounded-2xl border border-dark-600/50 shadow-2xl relative overflow-hidden" x-data="providerFormEdit('{{ strtolower($apiProvider->code ?: 'digiflazz') }}')">
            @csrf
            @method('PUT')

            <div class="absolute top-0 right-0 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl -z-10 -mr-20 -mt-20 pointer-events-none"></div>

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Provider <span class="text-rose-500">*</span></label>
                        <select name="provider" x-model="provider" @change="syncDefaults" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 block p-3 transition-colors">
                            <option value="rajabiller">Rajabiller</option>
                            <option value="digiflazz">Digiflazz</option>
                            <option value="orderkuota">OrderKuota</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Kode Provider <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" x-model="code" value="{{ old('code', $apiProvider->code) }}" required class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 block p-3 transition-colors lowercase font-mono">
                        @error('code')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Nama Provider <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $apiProvider->name) }}" x-model="name" required class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 block p-3 transition-colors">
                    @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="bg-dark-800/30 p-5 rounded-xl border border-dark-700">
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Kredensial API</label>
                    <p class="text-[11px] text-gray-500 mb-3">Nilai lama dimasking jadi *****. Isi hanya field yang ingin diganti.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="provider === 'rajabiller'">
                        <div>
                            <label class="block text-gray-400 text-xs mb-1 font-bold">UID / User ID Outlet <span class="text-rose-500">*</span></label>
                            <input type="text" name="credentials[uid]" value="{{ $maskedCredentials['uid'] ?? '' }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs" placeholder="*****">
                            <p class="text-gray-600 text-[10px] mt-1">User ID outlet dari dashboard Rajabiller</p>
                        </div>
                        <div>
                            <label class="block text-gray-400 text-xs mb-1 font-bold">PIN Transaksi <span class="text-rose-500">*</span></label>
                            <input type="password" name="credentials[pin]" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs" placeholder="Isi untuk mengganti PIN">
                            <p class="text-gray-600 text-[10px] mt-1">Kosongkan jika tidak ingin mengubah PIN</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-400 text-xs mb-1 font-bold">Environment</label>
                            <select name="credentials[env]" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                                <option value="production" {{ ($maskedCredentials['env'] ?? 'production') === 'production' ? 'selected' : '' }}>🟢 Production – api.rajabiller.com</option>
                                <option value="sandbox"    {{ ($maskedCredentials['env'] ?? '') === 'sandbox' ? 'selected' : '' }}>🟡 Sandbox / Dev – c-dev-api.rajabiller.com</option>
                            </select>
                        </div>
                    </div>

                    <div x-show="provider === 'digiflazz'" style="display:none;">

                        {{-- Guide Box --}}
                        <div class="mb-4 p-4 bg-blue-950/30 border border-blue-700/30 rounded-xl text-xs text-blue-300 space-y-1">
                            <p class="font-bold text-blue-200 flex items-center gap-2"><i class="fas fa-info-circle"></i> Cara Mendapatkan Credentials Digiflazz</p>
                            <ol class="list-decimal list-inside space-y-1 text-blue-300/80 mt-2">
                                <li>Login ke <span class="font-mono text-blue-200">member.digiflazz.com</span></li>
                                <li>Klik menu <strong>Pengaturan Koneksi API</strong></li>
                                <li>Salin <strong>Username</strong> dan <strong>API Key</strong></li>
                                <li>Tambahkan Whitelist IP jika diperlukan oleh penyedia</li>
                            </ol>
                            <div class="mt-3 pt-3 border-t border-blue-700/30">
                                <p class="font-bold text-blue-200 flex items-center gap-2"><i class="fas fa-webhook"></i> Webhook URL untuk Digiflazz</p>
                                <p class="mt-1">Daftarkan URL ini di tab <strong>Webhook</strong> pada dashboard Digiflazz:</p>
                                <code class="block mt-1 bg-dark-900 px-3 py-2 rounded text-emerald-400 font-mono break-all select-all">{{ url('/api/webhook/digiflazz') }}</code>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-400 text-xs mb-1 font-bold">Username Digiflazz <span class="text-rose-500">*</span></label>
                                <input type="text" name="credentials[username]" value="{{ $maskedCredentials['username'] ?? '' }}"
                                    class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs"
                                    placeholder="username dari dashboard Digiflazz">
                                <p class="text-gray-600 text-[10px] mt-1">Username akun member Digiflazz (bukan email)</p>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs mb-1 font-bold">API Key (Production) <span class="text-rose-500">*</span></label>
                                <input type="password" name="credentials[api_key]" value=""
                                    class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs"
                                    placeholder="Isi untuk mengganti API Key">
                                <p class="text-gray-600 text-[10px] mt-1">Kosongkan jika tidak ingin mengubah API Key</p>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs mb-1 font-bold">Environment</label>
                                <select name="credentials[env]" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                                    <option value="production" {{ ($maskedCredentials['env'] ?? 'production') === 'production' ? 'selected' : '' }}>🟢 Production – api.digiflazz.com</option>
                                    <option value="sandbox" {{ ($maskedCredentials['env'] ?? '') === 'sandbox' ? 'selected' : '' }}>🟡 Sandbox – Development Mode</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs mb-1 font-bold">Custom Base URL <span class="text-gray-600 font-normal">(Opsional)</span></label>
                                <input type="text" name="credentials[url]" value="{{ $maskedCredentials['url'] ?? '' }}"
                                    class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs"
                                    placeholder="https://api.digiflazz.com/v1/">
                                <p class="text-gray-600 text-[10px] mt-1">Kosongkan untuk pakai URL default Digiflazz</p>
                            </div>
                        </div>
                    </div>


                    <div x-show="provider === 'orderkuota'" style="display:none;">
                        <div class="mb-4 p-4 bg-green-950/30 border border-green-700/30 rounded-xl text-xs text-green-300 space-y-1">
                            <p class="font-bold text-green-200 flex items-center gap-2"><i class="fas fa-info-circle"></i> OkeConnect H2H (OrderKuota)</p>
                            <p class="text-green-300/80">Center Utama: <code>h2h.okeconnect.com</code> | Backup: <code>b2b.okeconnect.com</code></p>
                            <div class="mt-2 pt-2 border-t border-green-700/30">
                                <p class="font-bold text-green-200">URL Callback:</p>
                                <code class="block mt-1 bg-dark-900 px-3 py-2 rounded text-emerald-400 font-mono break-all select-all">{{ url('/webhook/provider/orderkuota') }}</code>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-400 text-xs mb-1 font-bold">Member ID <span class="text-rose-500">*</span></label>
                                <input type="text" name="credentials[member_id]" value="{{ $maskedCredentials['member_id'] ?? '' }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs" placeholder="OK00123">
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs mb-1 font-bold">PIN Transaksi (4 digit) <span class="text-rose-500">*</span></label>
                                <input type="password" name="credentials[pin]" value="{{ $maskedCredentials['pin'] ?? '' }}" inputmode="numeric" maxlength="4" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs" placeholder="*****">
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs mb-1 font-bold">Password H2H <span class="text-rose-500">*</span></label>
                                <input type="password" name="credentials[password]" value="{{ $maskedCredentials['password'] ?? '' }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs" placeholder="*****">
                            </div>
                            <div>
                                <label class="block text-gray-400 text-xs mb-1 font-bold">Custom Base URL <span class="text-gray-600 font-normal">(Opsional)</span></label>
                                <input type="text" name="credentials[url]" value="{{ $maskedCredentials['url'] ?? '' }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono text-xs" placeholder="https://h2h.okeconnect.com/trx/">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-800/30 p-5 rounded-xl border border-dark-700">
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Verifikasi Keamanan</label>
                    <p class="text-[11px] text-gray-500 mb-3">Untuk mengganti API key, wajib password admin dan PIN admin.</p>

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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-400 text-xs mb-1">Password Admin</label>
                            <input type="password" name="current_password" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Wajib saat ubah credential">
                            @error('current_password')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            @if($hasApiPin)
                                <label class="block text-gray-400 text-xs mb-1">PIN Admin (6 digit)</label>
                                <input type="password" name="security_pin" inputmode="numeric" maxlength="6" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="******">
                                @error('security_pin')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                            @else
                                <label class="block text-gray-400 text-xs mb-1">PIN Baru (6 digit)</label>
                                <input type="password" name="new_pin" inputmode="numeric" maxlength="6" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Buat PIN baru">
                                @error('new_pin')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                            @endif
                        </div>
                    </div>

                    @if(!$hasApiPin)
                        <div class="mt-3">
                            <label class="block text-gray-400 text-xs mb-1">Konfirmasi PIN Baru</label>
                            <input type="password" name="new_pin_confirmation" inputmode="numeric" maxlength="6" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Ulangi PIN baru">
                        </div>
                    @endif
                </div>

                <div class="bg-dark-800/30 p-5 rounded-xl border border-dark-700">
                    <div class="mb-4">
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-3">Logo Provider</label>
                        <div class="flex items-center gap-4">
                            @if($apiProvider->logo)
                                <img src="{{ asset('storage/' . $apiProvider->logo) }}" class="w-16 h-16 object-cover rounded-xl shadow-md border border-dark-600">
                            @endif
                            <div class="flex-1">
                                <input type="file" name="logo" accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 file:transition-colors cursor-pointer block bg-dark-900 border border-dark-600 rounded-xl p-2 focus:outline-none">
                                <p class="text-[11px] text-gray-500 mt-2">* Kosongkan jika tidak ingin mengubah logo saat ini.</p>
                            </div>
                        </div>
                        @error('logo')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Deskripsi Keterangan</label>
                        <textarea name="description" rows="3" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500 block p-3 transition-colors">{{ old('description', $apiProvider->description) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center pt-2 border-t border-dark-700/50 pt-5 mt-2">
                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $apiProvider->is_active) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-dark-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500 border border-dark-600"></div>
                        <span class="ml-3 text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Aktivasi Provider (On/Off)</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-dark-700/50">
                <a href="{{ route('admin.api-providers.index') }}" class="px-5 py-2.5 bg-dark-800 rounded-xl text-sm font-bold text-gray-400 hover:text-white hover:bg-dark-700 border border-dark-600 transition-all shadow-sm">Batal</a>
                <button type="submit" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 transform transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i> Perbarui Provider
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function providerFormEdit(currentCode) {
        const code = (currentCode || '').toLowerCase();
        let provider = 'digiflazz';
        if (code === 'rajabiller') provider = 'rajabiller';
        if (code === 'orderkuota' || code === 'orderkouta') provider = 'orderkuota';

        return {
            provider,
            code: code || provider,
            name: '{{ addslashes($apiProvider->name) }}',
            syncDefaults() {
                if (this.provider === 'rajabiller') {
                    this.code = 'rajabiller';
                    this.name = 'Rajabiller';
                } else if (this.provider === 'digiflazz') {
                    this.code = 'digiflazz';
                    this.name = 'Digiflazz';
                } else {
                    this.code = 'orderkuota';
                    this.name = 'OrderKuota';
                }
            }
        }
    }
</script>
@endpush
@endsection
