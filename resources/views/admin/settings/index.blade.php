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

                    <div class="pt-2 border-t border-dark-700/50 mt-4">
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Favicon Web (Tab Browser)</label>
                        <input type="file" name="site_favicon" accept="image/*" @change="onFaviconChange" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-500/10 file:text-emerald-400 hover:file:bg-emerald-500/20 block bg-dark-900 border border-dark-600 rounded-xl p-2 focus:outline-none">
                        <p class="text-xs text-gray-500 mt-2">Tampil mungil di tab browser & hasil pencarian Google. Gambar akan di-resize otomatis 1:1.</p>
                        @error('site_favicon')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror

                        <label class="inline-flex items-center gap-2 mt-3 text-sm text-gray-300 cursor-pointer block">
                            <input type="checkbox" name="remove_site_favicon" value="1" x-model="removeFavicon" @change="onToggleRemoveFavicon" class="rounded border-dark-600 bg-dark-900 text-rose-500 focus:ring-rose-500/40">
                            <span>Hapus favicon saat ini</span>
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

                        <p class="text-white text-lg font-black tracking-tight flex items-center gap-2">
                            <img x-show="previewFavicon" :src="previewFavicon" class="w-5 h-5 rounded object-cover shadow border border-dark-600">
                            <span x-text="previewName || 'PPOBKu'"></span>
                        </p>
                        <p class="text-gray-400 text-xs mt-1" x-text="previewDescription || 'Platform topup game dan PPOB termurah dan terpercaya.'"></p>
                    </div>
                </div>
            </div>
            
            <!-- Auth Screen Branding -->
            <h3 class="text-lg font-bold text-white mt-8 border-b border-dark-700 pb-2 mb-6">Tampilan Layar Login & Register</h3>
            <p class="text-sm text-gray-400 mb-4">Sisi kiri dari form otentikasi dapat Anda modifikasi gambarnya dan teks promosinya.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Judul Teks (Utama)</label>
                        <input type="text" name="auth_title" value="{{ old('auth_title', $settings['auth_title'] ?? '') }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Contoh: Selamat Datang di PPOBKu">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Sub Judul Teks (Tagline)</label>
                        <textarea name="auth_subtitle" rows="3" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Platform unggulan terbaik...">{{ old('auth_subtitle', $settings['auth_subtitle'] ?? '') }}</textarea>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Gambar Background Latar</label>
                    <input type="file" name="auth_cover_image" accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 block bg-dark-900 border border-dark-600 rounded-xl p-2 focus:outline-none mb-3">
                    <div class="mt-2 text-[11px] text-brand-300 bg-brand-500/10 border border-brand-500/20 p-3 rounded-xl leading-relaxed">
                        <i class="fas fa-info-circle mr-1 text-brand-400"></i> <strong>GUIDE:</strong> Untuk hasil potret layar komputer (Desktop) terbaik, <strong>rasio ukuran yang ideal adalah 1080 x 1080px (Persegi 1:1) hingga 1080 x 1920px (Potret memanjang).</strong><br> Format gambar wajib berupa JPG, JPEG, PNG, atau WEBP dengan ukuran maksimum berkas 2 Megabytes. <span class="text-gray-400">Bila ingin menggunakan efek gradient menyala bawaan web, biarkan form ini kosong dan hapus centang di bawah.</span>
                    </div>
                    
                    @if(!empty($settings['auth_cover_image']))
                    <div class="mt-3 bg-dark-800 border border-dark-700 p-2 rounded-xl inline-block relative">
                        <img src="{{ asset('storage/' . $settings['auth_cover_image']) }}" class="h-24 rounded object-cover shadow-lg">
                    </div>
                    @endif
                    
                    <label class="inline-flex items-center gap-2 mt-3 text-sm text-gray-300 cursor-pointer block">
                        <input type="checkbox" name="remove_auth_cover" value="1" class="rounded border-dark-600 bg-dark-900 text-rose-500 focus:ring-rose-500/40">
                        <span>Hapus Cover (Gunakan Polos)</span>
                    </label>
                </div>
            </div>

            <!-- Custom HTML Section -->
            <div class="mt-6 border border-brand-500/20 bg-dark-800/50 p-5 rounded-2xl">
                <label class="block text-brand-400 text-sm font-bold uppercase tracking-wider mb-3"><i class="fas fa-code mr-2"></i>Custom HTML Render (Lanjutan)</label>
                <p class="text-xs text-gray-400 mb-3 block">Form khusus diletakkan di bawah Judul/Tagline. Gunakan blok p, div, span, b, iframe, dan style sepuasnya untuk menambah dekorasi ekstra di Auth Layout.</p>
                <textarea name="auth_custom_html" rows="5" class="w-full bg-dark-900 font-mono text-emerald-400 border border-dark-600 text-sm rounded-xl p-4" placeholder="<div class='mt-5 p-4 bg-white/5 rounded-xl'><h3>Promo Spesial!</h3><p>Dapatkan diskon 50%...</p></div>">{{ old('auth_custom_html', $settings['auth_custom_html'] ?? '') }}</textarea>
                <div class="mt-3 text-[11px] text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 p-3 rounded-xl leading-relaxed">
                    <i class="fas fa-lightbulb mr-1 text-emerald-400"></i> <strong>GUIDE GAMBAR HTML:</strong> Apabila lu menyisipkan gambar promo/banner via tag HTML <code>&lt;img&gt;</code>, sangat disarankan menggunakan atribut class Tailwind seperti <code>class="w-full rounded-2xl shadow-lg"</code> atau inline css <code>style="width:100%; border-radius:1rem;"</code> agar gambar otomatis menyesuaikan lebar layar (Responsif Mobile & PC). <br><strong>Ukuran Rekomendasi:</strong> Lebar dimensi optimal <strong>600px hingga 800px</strong> bebas tinggi (Potret/Persegi/Landscape) agar terlihat elegan di Guest Panel.
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

            <h3 class="text-lg font-bold text-white mt-8 border-b border-dark-700 pb-2 mb-6">Sistem Harga & Keuntungan (Markup)</h3>

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
                        <p class="text-xs text-gray-400 ml-8">Admin atur harga jual secara manual per produk.</p>
                    </label>
                    <label @click="pricingMode = 'cheapest_auto'" :class="pricingMode === 'cheapest_auto' ? 'border-emerald-500 bg-emerald-500/10 ring-2 ring-emerald-500/30' : 'border-dark-600 bg-dark-900 hover:border-dark-500'" class="cursor-pointer rounded-xl border p-4 transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div :class="pricingMode === 'cheapest_auto' ? 'bg-emerald-500 border-emerald-500' : 'bg-dark-700 border-dark-600'" class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition">
                                <div x-show="pricingMode === 'cheapest_auto'" class="w-2.5 h-2.5 bg-white rounded-full"></div>
                            </div>
                            <span class="text-white font-bold">⚡ Termurah Auto</span>
                        </div>
                        <p class="text-xs text-gray-400 ml-8">Harga jual = Harga Modal + Keuntungan Default di bawah.</p>
                    </label>
                </div>
                <input type="hidden" name="pricing_mode" :value="pricingMode">
            </div>

            <div class="mt-6">
                <h4 class="text-sm font-bold text-white mb-4">Pengaturan Keuntungan Default (Khusus Prabayar / Prepaid)</h4>
                <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 mb-5">
                    <p class="text-xs text-amber-200/90 leading-relaxed">
                        <i class="fas fa-info-circle mr-1"></i> <strong>Penting:</strong> Pengaturan keuntungan ini hanya berlaku untuk produk <strong>Prabayar (Pulsa, Game, E-Wallet)</strong>. <br>
                        Untuk produk <strong>Pascabayar (Tagihan PPOB/Multifinance)</strong>, fee admin sudah ditentukan oleh pihak Provider API dan harga jual tidak akan di-markup.
                    </p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Tipe Keuntungan</label>
                        <select name="default_commission_type" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                            <option value="percentage" {{ old('default_commission_type', $settings['default_commission_type'] ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Persentase (% / Dari Harga Modal)</option>
                            <option value="flat" {{ old('default_commission_type', $settings['default_commission_type'] ?? '') === 'flat' ? 'selected' : '' }}>Nominal Tetap (+ Rp)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Nilai Keuntungan</label>
                        <input type="number" step="0.01" name="default_commission_value" value="{{ old('default_commission_value', $settings['default_commission_value'] ?? '0') }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" placeholder="Contoh: 5 untuk 5% atau 500 untuk Rp 500">
                    </div>
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

                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <button type="button" @click="startBot" class="px-4 py-2 bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-lg text-xs font-bold hover:bg-blue-500/30 transition">
                            <i class="fas fa-play mr-1"></i> Start Bot
                        </button>
                        <button type="button" @click="stopBot" class="px-4 py-2 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-lg text-xs font-bold hover:bg-rose-500/30 transition">
                            <i class="fas fa-stop mr-1"></i> Stop Bot
                        </button>
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

            {{-- OTP & Wallet --}}
            <h3 class="text-lg font-bold text-white mt-8 border-b border-dark-700 pb-2 mb-6">Sistem OTP & Saldo Wallet</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Label Saldo User</label>
                    <input type="text" name="wallet_label" value="{{ old('wallet_label', $settings['wallet_label'] ?? 'Saldo') }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3">
                    <p class="text-xs text-gray-400 mt-1">Penamaan saldo (Misal: Saldo, UC, Diamonds)</p>
                    @error('wallet_label')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Panjang Kode OTP</label>
                    <input type="number" name="otp_length" value="{{ old('otp_length', $settings['otp_length'] ?? '6') }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" min="4" max="8">
                    <p class="text-xs text-gray-400 mt-1">Direkomendasikan 6 digit</p>
                    @error('otp_length')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Masa Aktif OTP (Menit)</label>
                    <input type="number" name="otp_expiry_minutes" value="{{ old('otp_expiry_minutes', $settings['otp_expiry_minutes'] ?? '5') }}" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3" min="1" max="60">
                    <p class="text-xs text-gray-400 mt-1">Direkomendasikan 3-5 menit</p>
                    @error('otp_expiry_minutes')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6" x-data="{ showGuide: false }">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider">Template Email OTP (HTML)</label>
                    <button type="button" @click="showGuide = !showGuide" class="text-xs bg-dark-800/80 hover:bg-dark-700 text-brand-400 px-3 py-1.5 rounded-lg border border-dark-600 transition flex items-center gap-1.5 font-bold">
                        <i class="fas fa-book-open"></i> <span x-text="showGuide ? 'Tutup Panduan' : 'Buka Panduan (Guide Book)'"></span>
                    </button>
                </div>
                
                <!-- Expanded Guide Book -->
                <div x-show="showGuide" x-collapse x-cloak class="mb-4 bg-blue-900/10 border border-blue-500/20 rounded-xl p-4 sm:p-5">
                    <h4 class="text-blue-400 font-bold mb-3 text-sm"><i class="fas fa-lightbulb text-yellow-400 mr-1.5"></i> Panduan Membuat Template Email (Guide Book)</h4>
                    
                    <ul class="text-xs text-gray-300 space-y-2 list-disc pl-5 mb-4">
                        <li>Gunakan sintaks <strong>HTML tag biasa</strong> seperti <code class="bg-dark-900 px-1 py-0.5 rounded text-gray-400 text-[10px]">&lt;h1&gt;</code>, <code class="bg-dark-900 px-1 py-0.5 rounded text-gray-400 text-[10px]">&lt;p&gt;</code>, atau <code class="bg-dark-900 px-1 py-0.5 rounded text-gray-400 text-[10px]">&lt;div&gt;</code> untuk merangkai tampilan.</li>
                        <li>Pastikan menggunakan <strong>CSS Inline</strong> di atribut <code class="bg-dark-900 px-1 py-0.5 rounded text-gray-400 text-[10px]">style="..."</code>. Jangan pakai referensi file CSS eksternal karena klien email (seperti Gmail/Yahoo) sering memblokirnya.</li>
                        <li>Jangan gunakan <strong>JavaScript</strong> (<code class="bg-dark-900 px-1 py-0.5 rounded text-gray-400 text-[10px]">&lt;script&gt;</code>) kerena klien pembaca email manapun pasti akan memblokirnya secara otomatis atas alasan keamanan.</li>
                        <li>Simpan gambar atau logo sistem ke server publik lain atau gunakan URL absolut (e.g. <code class="bg-dark-900 px-1 py-0.5 text-blue-300 text-[10px]">https://domain.com/logo.png</code>).</li>
                    </ul>

                    <h5 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 border-b border-blue-500/10 pb-1">Daftar Variabel Penampung Nilai</h5>
                    <div class="flex flex-wrap gap-3">
                        <div class="bg-dark-900/50 border border-dark-700 rounded-lg p-2 flex items-center gap-3">
                            <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded font-mono font-bold border border-blue-500/30">{OTP}</span>
                            <span class="text-xs text-gray-400">Dicetak menjadi 4-8 digit Angka Unik OTP.</span>
                        </div>
                        <div class="bg-dark-900/50 border border-dark-700 rounded-lg p-2 flex items-center gap-3">
                            <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded font-mono font-bold border border-blue-500/30">{APP_NAME}</span>
                            <span class="text-xs text-gray-400">Dicetak menjadi Nama Website (Brand) di atas.</span>
                        </div>
                    </div>
                </div>

                <textarea name="email_otp_template" rows="8" class="w-full bg-dark-900 border border-dark-600 text-white text-sm rounded-xl p-3 font-mono" placeholder="Masukkan HTML template email...">{{ old('email_otp_template', $settings['email_otp_template'] ?? '<div style="font-family: sans-serif; padding: 20px; text-align: center; background: #f9f9f9; border-radius: 12px; max-width: 500px; margin: auto;">'."\n".'    <h2 style="color: #333;">Kode OTP Anda: {APP_NAME}</h2>'."\n".'    <h1 style="background: #eef; padding: 15px 25px; display: inline-block; letter-spacing: 5px; color: #1e40af; border-radius: 8px;">{OTP}</h1>'."\n".'    <p style="color: #666; font-size: 14px;">Jangan memberikan kode ini ke siapapun. Kode ini berlaku selama <strong style="color: #ef4444;">5 menit</strong>.</p>'."\n".'</div>') }}</textarea>
                @error('email_otp_template')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
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
            originalFavicon: @json(!empty($settings['site_favicon']) ? asset('storage/' . $settings['site_favicon']) : null),
            previewFavicon: @json(!empty($settings['site_favicon']) ? asset('storage/' . $settings['site_favicon']) : null),
            previewName: @json(old('site_name', $settings['site_name'] ?? 'PPOBKu')),
            previewDescription: @json(old('site_description', $settings['site_description'] ?? 'Platform topup game dan PPOB termurah dan terpercaya.')),
            removeLogo: false,
            removeFavicon: false,

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

            onFaviconChange(event) {
                const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
                if (!file) {
                    this.previewFavicon = this.removeFavicon ? null : this.originalFavicon;
                    return;
                }

                this.removeFavicon = false;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.previewFavicon = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            onToggleRemoveLogo() {
                if (this.removeLogo) {
                    this.previewLogo = null;
                    return;
                }

                this.previewLogo = this.originalLogo;
            },

            onToggleRemoveFavicon() {
                if (this.removeFavicon) {
                    this.previewFavicon = null;
                    return;
                }

                this.previewFavicon = this.originalFavicon;
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
            },

            async startBot() {
                this.statusText = '⏳ Memulai bot... (10s)';
                try {
                    const res = await fetch('{{ route("admin.whatsapp.start") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    await res.json();
                    // pm2 start takes a moment to boot the node app
                    setTimeout(() => this.checkStatus(), 5000);
                } catch (e) {
                    this.statusText = '❌ Error memulai bot';
                }
            },

            async stopBot() {
                this.statusText = '⏳ Mematikan bot...';
                try {
                    const res = await fetch('{{ route("admin.whatsapp.stop") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    await res.json();
                    setTimeout(() => this.checkStatus(), 2000);
                } catch (e) {
                    this.statusText = '❌ Error mematikan bot';
                }
            }
        };
    }
</script>
@endsection

