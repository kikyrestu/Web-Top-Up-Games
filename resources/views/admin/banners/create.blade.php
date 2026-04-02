@extends('layouts.admin')

@section('title', 'Tambah Banner')
@section('header', 'Tambah Banner')

@section('content')
<div class="flex justify-center w-full" x-data="bannerForm()">
    <div class="w-full max-w-4xl">
        <div class="glass-panel rounded-2xl shadow-xl border border-dark-700 p-6 sm:p-8 mb-8 relative overflow-hidden w-full">
            <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex items-center gap-4 mb-8 border-b border-dark-700 pb-5">
                <div class="w-12 h-12 rounded-xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center text-brand-400">
                    <i class="fas fa-bullhorn text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white tracking-tight">Banner Baru</h2>
                    <p class="text-sm text-gray-400 mt-1">Buat hero banner untuk promo atau pengumuman.</p>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="mb-8 border border-dark-600 rounded-xl overflow-hidden bg-dark-900 shadow-inner relative group">
                <div class="absolute top-0 left-0 bg-dark-700/80 backdrop-blur-sm text-gray-300 text-[10px] uppercase font-bold px-3 py-1.5 rounded-br-lg z-10 border-b border-r border-dark-600">
                    Live Preview
                </div>
                <!-- Box preview (aspect ratio: 21:9 utk desktop standard banner) -->
                <div class="w-full aspect-[21/9] md:aspect-[3/1] bg-dark-800 flex items-center justify-center overflow-hidden relative" id="preview-container">
                    
                    <template x-if="mediaType === 'image'">
                        <div class="w-full h-full">
                            <img :src="imageUrl" x-show="imageUrl" class="w-full h-full object-cover">
                            <div x-show="!imageUrl" class="w-full h-full flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-image text-3xl mb-2"></i>
                                <span class="text-sm">Pilih & Upload Gambar</span>
                            </div>
                        </div>
                    </template>

                    <template x-if="mediaType === 'video'">
                        <div class="w-full h-full">
                            <video :src="videoUrl" x-show="videoUrl" class="w-full h-full object-cover" autoplay loop muted playsinline></video>
                            <div x-show="!videoUrl" class="w-full h-full flex flex-col items-center justify-center text-gray-500">
                                <i class="fas fa-video text-3xl mb-2"></i>
                                <span class="text-sm">Pilih & Upload Video</span>
                            </div>
                        </div>
                    </template>

                    <template x-if="mediaType === 'embed' || mediaType === 'html'">
                        <iframe class="w-full h-full border-0 bg-transparent" :srcdoc="mediaContent" sandbox="allow-scripts allow-same-origin allow-popups"></iframe>
                    </template>
                </div>
            </div>

            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="relative z-10 space-y-6">
                @csrf

                <!-- RAW DEBUG BLOCK -->
                <div class="bg-gray-900 border border-yellow-500/50 p-4 rounded-xl overflow-auto text-[10px] font-mono whitespace-pre-wrap text-yellow-300">
                    <strong>[RAW DEBUG SESSION & ERRORS]</strong>
                    {{ print_r(session()->all(), true) }}
                    <strong>[RAW ERRORS]</strong>
                    {{ print_r($errors->all(), true) }}
                </div>

                @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/50 text-red-400 p-4 rounded-xl shadow-sm text-sm">
                    <p class="font-bold flex items-center mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Terdapat kesalahan pada input:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Type Selection -->
                <div class="bg-dark-800/50 p-5 rounded-xl border border-dark-700">
                    <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-3">Tipe Media Banner</label>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="media_type" value="image" x-model="mediaType" class="peer sr-only" {{ old('media_type', 'image') == 'image' ? 'checked' : '' }}>
                            <div class="p-3 text-center rounded-xl bg-dark-900 border border-dark-600 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 peer-checked:text-brand-400 text-gray-400 transition-all hover:bg-dark-800 shadow-sm">
                                <i class="fas fa-image mb-2 text-lg block"></i>
                                <span class="text-xs font-bold">Gambar</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="media_type" value="video" x-model="mediaType" class="peer sr-only" {{ old('media_type') == 'video' ? 'checked' : '' }}>
                            <div class="p-3 text-center rounded-xl bg-dark-900 border border-dark-600 peer-checked:border-brand-500 peer-checked:bg-brand-500/10 peer-checked:text-brand-400 text-gray-400 transition-all hover:bg-dark-800 shadow-sm">
                                <i class="fas fa-video mb-2 text-lg block"></i>
                                <span class="text-xs font-bold">Video MP4</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="media_type" value="embed" x-model="mediaType" class="peer sr-only" {{ old('media_type') == 'embed' ? 'checked' : '' }}>
                            <div class="p-3 text-center rounded-xl bg-dark-900 border border-dark-600 peer-checked:border-accent-500 peer-checked:bg-accent-500/10 peer-checked:text-accent-500 text-gray-400 transition-all hover:bg-dark-800 shadow-sm">
                                <i class="fab fa-youtube mb-2 text-lg block"></i>
                                <span class="text-xs font-bold">Embed (YT)</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="media_type" value="html" x-model="mediaType" class="peer sr-only" {{ old('media_type') == 'html' ? 'checked' : '' }}>
                            <div class="p-3 text-center rounded-xl bg-dark-900 border border-dark-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-500 text-gray-400 transition-all hover:bg-dark-800 shadow-sm">
                                <i class="fas fa-code mb-2 text-lg block"></i>
                                <span class="text-xs font-bold">HTML + CSS</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="title" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Judul Banner <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all">
                        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="link" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">URL Tujuan (Pilihan)</label>
                        <input type="url" name="link" id="link" value="{{ old('link') }}" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all" placeholder="https://...">
                        @error('link')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mb-5">
                    <label for="position" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Posisi Penempatan Banner <span class="text-red-500">*</span></label>
                    <select name="position" id="position" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all appearance-none">
                        <option value="hero" {{ old('position') == 'hero' ? 'selected' : '' }}>🖼️ Hero Utama (Slider Banner Besar)</option>
                        <option value="ppob_promo" {{ old('position') == 'ppob_promo' ? 'selected' : '' }}>💳 Banner PPOB (Section Bayar & Tagihan)</option>
                        <option value="game_promo" {{ old('position') == 'game_promo' ? 'selected' : '' }}>🎮 Banner Game Promo</option>
                        <option value="popup" {{ old('position') == 'popup' ? 'selected' : '' }}>📢 Popup / Notifikasi</option>
                        <option value="sidebar" {{ old('position') == 'sidebar' ? 'selected' : '' }}>📌 Sidebar</option>
                    </select>
                    <p class="text-gray-500 text-[11px] mt-1.5"><i class="fas fa-info-circle mr-1"></i>Pilih <strong class="text-green-400">Banner PPOB</strong> agar muncul di section "Bayar & Tagihan" halaman utama.</p>
                    @error('position')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="order" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Urutan Tampil (Order)</label>
                        <input type="number" name="order" id="order" value="{{ old('order', 0) }}" class="w-full px-4 py-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all">
                    </div>
                    <div class="flex items-center pt-7">
                        <label class="relative inline-flex items-center cursor-pointer group">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-dark-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-500/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 border border-dark-600"></div>
                            <span class="ml-3 text-sm font-bold text-gray-300">Tampilkan Banner</span>
                        </label>
                    </div>
                </div>

                <div class="bg-dark-800/30 p-5 rounded-xl border border-dark-700">
                    <div x-show="mediaType === 'image'" x-transition.opacity>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Upload Gambar <span class="text-red-500">*</span></label>
                        <input type="file" name="image" @change="previewFile" accept="image/*" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 file:transition-colors cursor-pointer">
                        <div class="mt-3 p-3 bg-dark-900 border border-dark-600 rounded-lg shadow-sm">
                            <div class="flex gap-2 text-gray-400 text-xs">
                                <i class="fas fa-info-circle text-brand-400 mt-0.5"></i>
                                <div>
                                    <span class="font-bold text-gray-300">Panduan Resolusi & Kualitas:</span>
                                    <ul class="list-disc list-inside mt-1 ml-1 space-y-1">
                                        <li>Ukuran terbaik: <span class="text-white font-mono bg-dark-800 px-1 rounded border border-dark-600">1200x400 px</span> (Lebar x Tinggi).</li>
                                        <li>Ekstensi optimal: <span class="text-brand-400 font-bold">WEBP</span> (untuk speed ngebut), JPG, PNG.</li>
                                        <li>Batas file: <span class="text-red-400 font-bold">5 MB</span>.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div x-show="mediaType === 'video'" style="display: none;" x-transition.opacity>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Upload Video (MP4) <span class="text-red-500">*</span></label>
                        <input type="file" name="video" @change="previewFile" accept="video/mp4,video/webm" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 file:transition-colors cursor-pointer">
                        
                        <div class="mt-3 p-3 bg-dark-900 border border-dark-600 rounded-lg shadow-sm">
                            <div class="flex gap-2 text-gray-400 text-[11px] leading-relaxed">
                                <i class="fas fa-magic text-accent-500 mt-0.5"></i>
                                <div>
                                    <span class="font-bold text-gray-300">Rekomendasi Video Background:</span>
                                    <p class="mt-1">Gunakan resolusi <span class="text-white font-mono bg-dark-800 px-1 rounded">1920x1080 (1080p)</span>. Usahakan durasi video pendek tanpa suara (auto-loop). Ukuran mentok <span class="text-red-400 font-bold">20 MB</span>.</p>
                                </div>
                            </div>
                        </div>
                        @error('video')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div x-show="mediaType === 'embed' || mediaType === 'html'" style="display: none;" x-transition.opacity>
                        <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2" x-text="mediaType === 'embed' ? 'Iframe / Embed Code *' : 'HTML Code *'"></label>
                        <textarea name="media_content" x-model="mediaContent" rows="5" class="w-full p-4 bg-dark-900 border border-dark-600 text-brand-300 rounded-xl focus:outline-none focus:border-brand-500 font-mono text-xs" placeholder="Paste kode disini..."></textarea>
                        @error('media_content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6 pt-5 border-t border-dark-700">
                    <a href="{{ route('admin.banners.index') }}" class="px-5 py-2.5 bg-dark-800 rounded-xl text-sm font-bold text-gray-400 hover:text-white hover:bg-dark-700 border border-dark-600 transition-all">Batal</a>
                    <button type="submit" class="bg-gradient-to-r from-brand-500 to-brand-400 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 hover:-translate-y-0.5 transform transition-all">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function bannerForm() {
        return {
            mediaType: '{{ old('media_type', 'image') }}',
            imageUrl: '',
            videoUrl: '',
            mediaContent: '',
            
            previewFile(event) {
                const file = event.target.files[0];
                if (!file) {
                    this.imageUrl = '';
                    this.videoUrl = '';
                    return;
                }
                const url = URL.createObjectURL(file);
                if (this.mediaType === 'image') this.imageUrl = url;
                else if (this.mediaType === 'video') this.videoUrl = url;
            }
        }
    }
</script>
@endpush
@endsection