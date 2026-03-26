@extends('layouts.admin')

@section('title', 'Pengaturan Website')
@section('header', 'Pengaturan Website')

@section('content')
<div class="bg-dark-800/40 backdrop-blur-xl rounded-2xl border border-dark-600/50 shadow-2xl w-full max-w-5xl" x-data="brandingSettingsForm()">
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')
        
        <div class="p-6 md:p-8">
            <h3 class="text-xl font-black text-white tracking-tight mb-2">Branding Website</h3>
            <p class="text-sm text-gray-400 mb-6">Ubah nama dan logo website langsung dari sini. Header front/admin akan otomatis ikut update.</p>

            @if($errors->any())
                <div class="mb-5 bg-red-900/20 border border-red-700/40 rounded-xl p-3 text-xs text-red-200">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-5">
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Nama Website</label>
                        <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? 'PPOBKu') }}" x-model="previewName" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Contoh: PPOBKu">
                        @error('site_name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Deskripsi Website</label>
                        <textarea name="site_description" rows="3" x-model="previewDescription" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Tagline singkat website kamu">{{ old('site_description', $settings['site_description'] ?? 'Platform topup game dan PPOB termurah dan terpercaya.') }}</textarea>
                        @error('site_description')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Logo Website</label>
                        <input type="file" name="site_logo" accept="image/*" @change="onLogoChange" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 block bg-dark-900 border border-dark-600 rounded-xl p-2 focus:outline-none">
                        <p class="text-xs text-gray-500 mt-2">Format gambar disarankan PNG transparan. Kosongkan jika tidak ingin mengganti.</p>
                        @error('site_logo')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror

                        <label class="inline-flex items-center gap-2 mt-3 text-sm text-gray-300 cursor-pointer">
                            <input type="checkbox" name="remove_site_logo" value="1" x-model="removeLogo" @change="onToggleRemoveLogo" class="rounded border-dark-600 bg-dark-900 text-rose-500 focus:ring-rose-500/40">
                            <span>Hapus logo saat ini</span>
                        </label>
                    </div>
                </div>

                <div class="bg-dark-900 border border-dark-700 rounded-2xl p-4">
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-3">Preview Branding</p>
                    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4">
                        <img x-show="previewLogo" :src="previewLogo" alt="Logo Website" class="h-12 w-auto object-contain mb-3">
                        <div x-show="!previewLogo" class="w-12 h-12 rounded-xl bg-brand-500/20 border border-brand-500/30 flex items-center justify-center text-brand-400 mb-3">
                            <i class="fas fa-image"></i>
                        </div>

                        <p class="text-white text-lg font-black tracking-tight" x-text="previewName || 'PPOBKu'"></p>
                        <p class="text-gray-400 text-xs mt-1" x-text="previewDescription || 'Platform topup game dan PPOB termurah dan terpercaya.'"></p>
                    </div>
                </div>
            </div>
            
            <h3 class="text-lg font-bold text-white mt-8 border-b border-dark-700 pb-2 mb-6">Kontak & Dukungan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">WhatsApp CS</label>
                    <input type="text" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact_whatsapp'] ?? '08123456789') }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                    <p class="text-xs text-gray-400 mt-1">Gunakan format awalan 08 / 628</p>
                    @error('contact_whatsapp')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Email CS</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? 'cs@domain.com') }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                    @error('contact_email')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <h3 class="text-lg font-bold text-white mt-8 border-b border-dark-700 pb-2 mb-6">Smart Pricing</h3>

            <div class="space-y-5" x-data="{ pricingMode: '{{ old('pricing_mode', $settings['pricing_mode'] ?? 'manual') }}' }">
                {{-- Pricing Mode Toggle --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label @click="pricingMode = 'manual'" :class="pricingMode === 'manual' ? 'border-brand-500 bg-brand-500/10 ring-2 ring-brand-500/30' : 'border-dark-600 bg-dark-900 hover:border-dark-500'" class="cursor-pointer rounded-xl border p-4 transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div :class="pricingMode === 'manual' ? 'bg-brand-500 border-brand-500' : 'bg-dark-700 border-dark-600'" class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition">
                                <div x-show="pricingMode === 'manual'" class="w-2.5 h-2.5 bg-white rounded-full"></div>
                            </div>
                            <span class="text-white font-bold">Normal / Manual</span>
                        </div>
                        <p class="text-xs text-gray-400 ml-8">Admin set harga jual dan komisi sendiri per produk.</p>
                    </label>
                    <label @click="pricingMode = 'cheapest_auto'" :class="pricingMode === 'cheapest_auto' ? 'border-emerald-500 bg-emerald-500/10 ring-2 ring-emerald-500/30' : 'border-dark-600 bg-dark-900 hover:border-dark-500'" class="cursor-pointer rounded-xl border p-4 transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div :class="pricingMode === 'cheapest_auto' ? 'bg-emerald-500 border-emerald-500' : 'bg-dark-700 border-dark-600'" class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition">
                                <div x-show="pricingMode === 'cheapest_auto'" class="w-2.5 h-2.5 bg-white rounded-full"></div>
                            </div>
                            <span class="text-white font-bold">⚡ Termurah Auto</span>
                        </div>
                        <p class="text-xs text-gray-400 ml-8">Auto pilih provider termurah + markup%. Komisi = selisih harga otomatis tertinggi.</p>
                    </label>
                </div>
                <input type="hidden" name="pricing_mode" :value="pricingMode">

                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Markup Harga (%)</label>
                    <div class="relative rounded-xl w-full md:w-1/3">
                        <input type="number" step="0.01" name="markup_percentage" value="{{ old('markup_percentage', $settings['markup_percentage'] ?? '5') }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 pr-12">
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <span class="text-gray-400 sm:text-sm">%</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Persentase keuntungan untuk setiap produk saat sinkronisasi API.</p>
                </div>
            </div>

            {{-- Commission --}}
            <h3 class="text-lg font-bold text-white mt-8 border-b border-dark-700 pb-2 mb-6">Sistem Komisi</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Tipe Komisi Default</label>
                    <select name="default_commission_type" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                        <option value="percentage" {{ old('default_commission_type', $settings['default_commission_type'] ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        <option value="flat" {{ old('default_commission_type', $settings['default_commission_type'] ?? '') === 'flat' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Bisa di-override per kategori & per produk.</p>
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Nilai Komisi Default</label>
                    <input type="number" step="0.01" name="default_commission_value" value="{{ old('default_commission_value', $settings['default_commission_value'] ?? '0') }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Contoh: 5 untuk 5% atau 500 untuk Rp 500">
                    <p class="text-xs text-gray-400 mt-1">Kalau tipe Persentase, masukkan angka persen (misal 5). Kalau Nominal, masukkan Rupiah (misal 500).</p>
                </div>
            </div>

            {{-- Notification Bot --}}
            <h3 class="text-lg font-bold text-white mt-8 border-b border-dark-700 pb-2 mb-6">Notifikasi Bot</h3>

            <div class="space-y-6">
                {{-- Telegram --}}
                <div class="bg-dark-900 border border-dark-700 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center text-blue-400"><i class="fab fa-telegram-plane"></i></div>
                            <h4 class="text-white font-bold">Telegram Bot</h4>
                        </div>
                        <label class="relative inline-flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="telegram_enabled" value="0">
                            <input type="checkbox" name="telegram_enabled" value="1" {{ old('telegram_enabled', $settings['telegram_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-blue-500/40 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Bot Token</label>
                            <input type="text" name="telegram_bot_token" value="{{ old('telegram_bot_token', $settings['telegram_bot_token'] ?? '') }}" class="w-full bg-dark-800 border border-dark-600 text-white text-xs rounded-lg p-2.5" placeholder="123456:ABC-DEF...">
                            <p class="text-[10px] text-gray-500 mt-1">Dapatkan dari @BotFather di Telegram</p>
                        </div>
                        <div>
                            <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Chat ID</label>
                            <input type="text" name="telegram_chat_id" value="{{ old('telegram_chat_id', $settings['telegram_chat_id'] ?? '') }}" class="w-full bg-dark-800 border border-dark-600 text-white text-xs rounded-lg p-2.5" placeholder="-100123456789">
                            <p class="text-[10px] text-gray-500 mt-1">Chat/Group ID untuk menerima notifikasi</p>
                        </div>
                    </div>
                </div>

                {{-- WhatsApp --}}
                <div class="bg-dark-900 border border-dark-700 rounded-xl p-5" x-data="waBot()">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center text-green-400"><i class="fab fa-whatsapp"></i></div>
                            <h4 class="text-white font-bold">WhatsApp Bot</h4>
                        </div>
                        <label class="relative inline-flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="wa_enabled" value="0">
                            <input type="checkbox" name="wa_enabled" value="1" {{ old('wa_enabled', $settings['wa_enabled'] ?? '0') === '1' ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-green-500/40 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                        </label>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">URL WA Bot</label>
                        <input type="text" name="wa_bot_url" value="{{ old('wa_bot_url', $settings['wa_bot_url'] ?? 'http://localhost:3001') }}" class="w-full bg-dark-800 border border-dark-600 text-white text-xs rounded-lg p-2.5" placeholder="http://localhost:3001">
                    </div>

                    <div class="flex items-center gap-3 mb-4">
                        <button type="button" @click="fetchQr" class="px-4 py-2 bg-green-500/20 text-green-400 border border-green-500/30 rounded-lg text-xs font-bold hover:bg-green-500/30 transition">
                            <i class="fas fa-qrcode mr-1"></i> Tampilkan QR
                        </button>
                        <button type="button" @click="checkStatus" class="px-4 py-2 bg-dark-700 text-gray-300 rounded-lg text-xs font-bold hover:text-white transition">
                            <i class="fas fa-plug mr-1"></i> Cek Status
                        </button>
                        <span x-show="statusText" :class="connected ? 'text-emerald-400' : 'text-amber-400'" class="text-xs font-bold" x-text="statusText"></span>
                    </div>

                    {{-- QR Display --}}
                    <div x-show="qrImage" class="bg-white rounded-xl p-4 inline-block">
                        <img :src="qrImage" alt="WA QR Code" class="w-48 h-48">
                    </div>
                    <p x-show="qrError" class="text-red-400 text-xs" x-text="qrError"></p>
                </div>
            </div>

        </div>

        <div class="bg-dark-900/40 px-6 py-4 rounded-b-2xl border-t border-dark-700/60 text-right">
            <button type="submit" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 transform transition-all">
                <i class="fas fa-save mr-2"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<script>
    function brandingSettingsForm() {
        return {
            originalLogo: @json(!empty($settings['site_logo']) ? asset('storage/' . $settings['site_logo']) : null),
            previewLogo: @json(!empty($settings['site_logo']) ? asset('storage/' . $settings['site_logo']) : null),
            previewName: @json(old('site_name', $settings['site_name'] ?? 'PPOBKu')),
            previewDescription: @json(old('site_description', $settings['site_description'] ?? 'Platform topup game dan PPOB termurah dan terpercaya.')),
            removeLogo: false,

            onLogoChange(event) {
                const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
                if (!file) {
                    this.previewLogo = this.removeLogo ? null : this.originalLogo;
                    return;
                }

                this.removeLogo = false;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewLogo = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            onToggleRemoveLogo() {
                if (this.removeLogo) {
                    this.previewLogo = null;
                    return;
                }

                this.previewLogo = this.originalLogo;
            }
        };
    }

    function waBot() {
        return {
            qrImage: null,
            qrError: null,
            statusText: null,
            connected: false,

            async fetchQr() {
                this.qrError = null;
                try {
                    const res = await fetch('{{ route("admin.whatsapp.qr") }}');
                    const data = await res.json();
                    if (data.success && data.qr) {
                        this.qrImage = data.qr;
                    } else {
                        this.qrError = 'Tidak bisa ambil QR. Pastikan WA Bot sudah berjalan.';
                    }
                } catch (e) {
                    this.qrError = 'WA Bot tidak merespon. Pastikan server berjalan.';
                }
            },

            async checkStatus() {
                try {
                    const res = await fetch('{{ route("admin.whatsapp.status") }}');
                    const data = await res.json();
                    this.connected = data.connected || false;
                    this.statusText = data.connected ? '● Terhubung' : '○ Tidak terhubung';
                } catch (e) {
                    this.connected = false;
                    this.statusText = '○ Tidak terhubung';
                }
            }
        };
    }
</script>
@endsection

