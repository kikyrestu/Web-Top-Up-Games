@extends('layouts.admin')

@section('title', 'Edit Game / Layanan')
@section('header', 'Edit: ' . $category->name)

@section('content')
<div class="bg-gray-800 rounded-lg shadow-lg border border-gray-700 p-6 max-w-2xl" x-data="{ categoryType: '{{ old('type', $category->type) }}' }">
    <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="name" class="block text-gray-300 text-sm font-bold mb-2">Nama Kategori <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
            @error('name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="type" class="block text-gray-300 text-sm font-bold mb-2">Tipe / Platform <span class="text-red-500">*</span></label>
            <select name="type" id="type" x-model="categoryType" @change="$dispatch('type-changed', { type: categoryType })" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                <optgroup label="🎮 Game">
                    <option value="game" {{ old('type', $category->type) == 'game' ? 'selected' : '' }}>Game (Umum)</option>
                    <option value="seluler" {{ old('type', $category->type) == 'seluler' ? 'selected' : '' }}>Game Seluler (Mobile)</option>
                    <option value="pc" {{ old('type', $category->type) == 'pc' ? 'selected' : '' }}>Game PC</option>
                    <option value="voucher" {{ old('type', $category->type) == 'voucher' ? 'selected' : '' }}>Voucher</option>
                </optgroup>
                <optgroup label="💡 PPOB / Tagihan">
                    <option value="pulsa" {{ old('type', $category->type) == 'pulsa' ? 'selected' : '' }}>Pulsa</option>
                    <option value="paket_data" {{ old('type', $category->type) == 'paket_data' ? 'selected' : '' }}>Paket Data</option>
                    <option value="pln" {{ old('type', $category->type) == 'pln' ? 'selected' : '' }}>PLN / Token Listrik</option>
                    <option value="pdam" {{ old('type', $category->type) == 'pdam' ? 'selected' : '' }}>PDAM</option>
                    <option value="bpjs" {{ old('type', $category->type) == 'bpjs' ? 'selected' : '' }}>BPJS</option>
                    <option value="internet" {{ old('type', $category->type) == 'internet' ? 'selected' : '' }}>Internet / TV Kabel</option>
                    <option value="emoney" {{ old('type', $category->type) == 'emoney' ? 'selected' : '' }}>E-Money / E-Wallet</option>
                    <option value="ppob" {{ old('type', $category->type) == 'ppob' ? 'selected' : '' }}>PPOB Lainnya</option>
                </optgroup>
            </select>
            @error('type')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4" x-show="['game','seluler','pc','voucher'].includes(categoryType)" x-transition>
            <label for="publisher" class="block text-gray-300 text-sm font-bold mb-2">Publisher / Developer</label>
            <input type="text" name="publisher" id="publisher" value="{{ old('publisher', $category->publisher) }}" placeholder="contoh: Moonton" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            @error('publisher')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

<div class="mb-4" x-data="{ openIcon: false, selectedIcon: '{{ old('icon', $category->icon) }}', icons: ['fas fa-mobile-alt', 'fas fa-gamepad', 'fas fa-bolt', 'fas fa-wifi', 'fas fa-tv', 'fas fa-money-bill-wave', 'fas fa-credit-card', 'fas fa-wallet', 'fas fa-receipt', 'fas fa-laptop', 'fas fa-desktop', 'fas fa-headset', 'fas fa-fire', 'fas fa-star', 'fas fa-crown', 'fas fa-gem', 'fas fa-ticket-alt', 'fas fa-play', 'fas fa-gift', 'fas fa-phone'] }">
              <label for="icon" class="block text-gray-300 text-sm font-bold mb-2">Icon (Class FontAwesome)</label>
              
              <div class="relative">
                  <div class="flex">
                      <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-600 bg-gray-700 text-gray-300 sm:text-sm">
                          <i :class="selectedIcon ? selectedIcon : 'fas fa-icons'"></i>
                      </span>
                      <input type="text" name="icon" id="icon" x-model="selectedIcon" placeholder="contoh: fas fa-mobile-alt" class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none bg-gray-700 border-gray-600 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                      <button type="button" @click="openIcon = !openIcon" class="ml-0 rounded-r-md border border-l-0 border-indigo-600 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 focus:outline-none transition">
                          Pilih Ikon
                      </button>
                  </div>

                  <!-- Icon Picker Dropdown -->
                  <div x-show="openIcon" @click.away="openIcon = false" class="absolute z-10 mt-1 w-full bg-gray-800 rounded-md border border-gray-600 shadow-lg p-3">
                      <p class="text-xs text-gray-400 mb-2">Ikon Populer (klik untuk memilih):</p>
                      <div class="grid grid-cols-5 gap-2 max-h-48 overflow-y-auto p-1">
                          <template x-for="icon in icons" :key="icon">
                              <button type="button" @click="selectedIcon = icon; openIcon = false" class="p-2 bg-gray-700 hover:bg-indigo-600 hover:text-white rounded border border-gray-600 transition flex items-center justify-center text-gray-300 group" :title="icon">
                                  <i :class="icon" class="text-lg"></i>
                              </button>
                          </template>
                      </div>
                      <div class="mt-3 text-xs text-gray-400 pt-2 border-t border-gray-700">
                          Atau ketik manual class dari <a href="https://fontawesome.com/v6/search?m=free" target="_blank" class="text-blue-500 hover:underline">FontAwesome 6</a>.
                      </div>
                  </div>
              </div>

            @error('icon')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="thumbnail" class="block text-gray-300 text-sm font-bold mb-2">Thumbnail / Gambar Banner</label>
            @if($category->thumbnail)
                <div class="mb-3">
                    <img src="{{ Storage::url($category->thumbnail) }}" alt="Thumbnail Saat Ini" class="h-24 w-auto object-cover rounded shadow">
                    <p class="text-xs text-gray-400 mt-1">Thumbnail saat ini</p>
                </div>
            @endif
            <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            <p class="text-gray-400 text-xs mt-1">Kosongkan jika tidak ingin mengubah gambar. Max 2MB.</p>
            @error('thumbnail')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-300 text-sm font-bold mb-2">Deskripsi</label>
            <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">{{ old('description', $category->description) }}</textarea>
        </div>

        <div class="mb-4 flex space-x-4">
            <div class="w-1/2">
                <label for="sort_order" class="block text-gray-300 text-sm font-bold mb-2">Urutan Tampil</label>   
                <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>
            <div class="w-1/2 flex items-end pb-2">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" class="form-checkbox h-5 w-5 text-blue-600" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                    <span class="text-sm font-bold text-gray-300">Aktif</span>
                </label>
            </div>
        </div>

        <!-- Override Keuntungan per Kategori -->
        <div class="mb-4 border border-gray-600 rounded-lg p-4 bg-gray-900/50">
            <label class="block text-gray-300 text-sm font-bold mb-1"><i class="fas fa-coins mr-1"></i> Override Keuntungan (Opsional)</label>
            <p class="text-gray-500 text-xs mb-3">Kosongkan untuk menggunakan pengaturan keuntungan global dari Settings. Hanya berlaku untuk produk prabayar.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-400 text-xs font-bold mb-1">Tipe Keuntungan</label>
                    <select name="commission_type" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
                        <option value="" {{ empty(old('commission_type', $category->commission_type)) ? 'selected' : '' }}>-- Ikut Global --</option>
                        <option value="percentage" {{ old('commission_type', $category->commission_type) === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        <option value="flat" {{ old('commission_type', $category->commission_type) === 'flat' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-400 text-xs font-bold mb-1">Nilai Keuntungan</label>
                    <input type="number" step="0.01" name="commission_value" value="{{ old('commission_value', $category->commission_value) }}" placeholder="Contoh: 5 untuk 5% atau 500" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-sm">
                </div>
            </div>
        </div>

        <div class="mb-4 flex space-x-4">
            <div class="w-1/2 flex items-end pb-2">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_popular" value="1" class="form-checkbox h-5 w-5 text-yellow-500" {{ old('is_popular', $category->is_popular) ? 'checked' : '' }}>
                    <span class="text-sm font-bold text-gray-300">Tampilkan sebagai Populer</span>
                </label>
            </div>
            <div class="w-1/2 flex items-end pb-2">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="is_new" value="1" class="form-checkbox h-5 w-5 text-green-500" {{ old('is_new', $category->is_new) ? 'checked' : '' }}>
                    <span class="text-sm font-bold text-gray-300">Tampilkan label "Baru"</span>
                </label>
            </div>
        </div>

        <!-- Form Template Preset -->
        <div class="mb-4 border border-gray-600 rounded-lg p-4 bg-gray-900/50" x-data="formTemplateBuilder({{ json_encode($category->input_fields ?? []) }})" @type-changed.window="handleTypeChange($event.detail.type)">
            <label class="block text-gray-300 text-sm font-bold mb-1">
                <i class="fas fa-list-alt mr-1"></i> Template Form Pelanggan
            </label>
            <p class="text-gray-500 text-xs mb-3">Pilih template yang sesuai dengan jenis kategori. Ini menentukan form yang harus diisi customer saat checkout.</p>

            <select x-model="selectedPreset" @change="applyPreset()" class="w-full px-3 py-2 bg-gray-700 border-gray-600 text-white rounded focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 mb-3">
                <option value="">-- Pilih Template --</option>
                <optgroup label="🎮 Game Top-Up">
                    <option value="userid_server">User ID + Server/Zone (MLBB, Genshin Impact)</option>
                    <option value="userid_only">User ID saja (Free Fire, PUBG Mobile)</option>
                    <option value="username">Username (Roblox)</option>
                    <option value="voucher">Voucher / Kode (tanpa input — Steam, PSN, Xbox)</option>
                </optgroup>
                <optgroup label="💡 PPOB / Tagihan">
                    <option value="nomor_hp">Nomor HP — auto detect provider (Pulsa, Paket Data)</option>
                    <option value="id_pelanggan">ID / Nomor Pelanggan (PLN, PDAM, Internet)</option>
                    <option value="nomor_peserta">Nomor Peserta (BPJS)</option>
                </optgroup>
                <option value="custom">⚙️ Custom — Atur Sendiri</option>
            </select>

            <div x-show="fields.length > 0" class="mb-3">
                <p class="text-gray-400 text-xs font-bold mb-2"><i class="fas fa-eye mr-1"></i> Preview: Field yang akan tampil ke customer</p>
                <div class="bg-gray-800 rounded p-3 border border-gray-700 space-y-2">
                    <template x-for="(field, index) in fields" :key="index">
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-check-circle text-green-400 text-xs"></i>
                            <span class="text-white" x-text="field.label"></span>
                            <span class="text-gray-500 text-xs" x-text="'(' + field.placeholder + ')'"></span>
                            <span x-show="field.required" class="text-red-400 text-[10px] font-bold">WAJIB</span>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="fields.length === 0 && selectedPreset === 'voucher'" class="mb-3">
                <div class="bg-gray-800 rounded p-3 border border-gray-700">
                    <p class="text-green-400 text-sm"><i class="fas fa-info-circle mr-1"></i> Customer tidak perlu mengisi apapun — langsung pilih produk dan bayar.</p>
                </div>
            </div>

            <div x-show="selectedPreset === 'custom'" x-transition class="mt-4 border-t border-gray-700 pt-4">
                <div class="bg-blue-900/30 border border-blue-700/50 rounded-lg p-4 mb-4">
                    <h4 class="text-blue-300 text-sm font-bold mb-2"><i class="fas fa-book-open mr-1"></i> Panduan Mengisi Field Custom</h4>
                    <div class="text-gray-300 text-xs space-y-2">
                        <p><strong>Apa itu Field?</strong> Field adalah kotak isian yang harus diisi customer di halaman checkout.</p>
                        <ol class="list-decimal ml-4 space-y-1">
                            <li><strong>Tipe Data</strong>: <code class="text-yellow-300">target</code> = Data utama, <code class="text-yellow-300">target_zone</code> = Data tambahan</li>
                            <li><strong>Label</strong>: Judul yang ditampilkan (cth: "User ID")</li>
                            <li><strong>Placeholder</strong>: Contoh isian dalam kotak (cth: "Masukkan User ID")</li>
                            <li><strong>Wajib</strong>: Centang jika HARUS diisi</li>
                        </ol>
                    </div>
                </div>

                <template x-for="(field, index) in fields" :key="'custom-'+index">
                    <div class="flex flex-wrap items-end gap-2 mb-3 bg-gray-800 rounded p-3 border border-gray-700">
                        <div class="flex-1 min-w-[120px]">
                            <label class="text-gray-400 text-xs mb-1 block">Tipe Data</label>
                            <select x-model="field.name" class="w-full px-2 py-1.5 bg-gray-700 border-gray-600 text-white rounded text-sm">
                                <option value="target">target (Data Utama)</option>
                                <option value="target_zone">target_zone (Data Tambahan)</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[120px]">
                            <label class="text-gray-400 text-xs mb-1 block">Label (Judul)</label>
                            <input type="text" x-model="field.label" placeholder="cth: User ID" class="w-full px-2 py-1.5 bg-gray-700 border-gray-600 text-white rounded text-sm">
                        </div>
                        <div class="flex-1 min-w-[140px]">
                            <label class="text-gray-400 text-xs mb-1 block">Placeholder (Contoh)</label>
                            <input type="text" x-model="field.placeholder" placeholder="cth: Masukkan User ID" class="w-full px-2 py-1.5 bg-gray-700 border-gray-600 text-white rounded text-sm">
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-1 cursor-pointer">
                                <input type="checkbox" x-model="field.required" class="form-checkbox h-4 w-4 text-indigo-500">
                                <span class="text-gray-400 text-xs">Wajib</span>
                            </label>
                            <button type="button" @click="removeField(index)" class="text-red-400 hover:text-red-300 text-sm px-1">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="addField()" class="text-sm text-indigo-400 hover:text-indigo-300 flex items-center gap-1 mt-1">
                    <i class="fas fa-plus-circle"></i> Tambah Field Baru
                </button>
            </div>

            <input type="hidden" name="input_fields" :value="JSON.stringify(fields)">
        </div>

        <div class="flex justify-end space-x-3 mt-6">
            <a href="{{ route('admin.categories.index') }}" class="bg-gray-900/500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition">Batal</a>
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition">Simpan Perubahan</button>
        </div>
    </form>
</div>

<script>
function formTemplateBuilder(existing = []) {
    const presets = {
        'userid_server': [
            { name: 'target', label: 'User ID', placeholder: 'Masukkan User ID kamu', required: true },
            { name: 'target_zone', label: 'Server / Zone ID', placeholder: 'Masukkan Zone ID', required: true }
        ],
        'userid_only': [
            { name: 'target', label: 'User ID / Player ID', placeholder: 'Masukkan ID kamu', required: true }
        ],
        'username': [
            { name: 'target', label: 'Username', placeholder: 'Masukkan username kamu', required: true }
        ],
        'voucher': [],
        'nomor_hp': [
            { name: 'target', label: 'Nomor HP', placeholder: 'Contoh: 08123456789', required: true }
        ],
        'id_pelanggan': [
            { name: 'target', label: 'ID Pelanggan / No. Meter', placeholder: 'Masukkan nomor pelanggan', required: true }
        ],
        'nomor_peserta': [
            { name: 'target', label: 'Nomor Peserta BPJS', placeholder: 'Masukkan nomor peserta', required: true }
        ],
    };
    function detectPreset(fields) {
        if (!fields || fields.length === 0) return '';
        const sig = fields.map(f => f.name).join(',');
        if (sig === 'target,target_zone') return 'userid_server';
        if (fields.length === 1 && fields[0].name === 'target') {
            const lbl = (fields[0].label || '').toLowerCase();
            if (lbl.includes('hp') || lbl.includes('nomor')) return 'nomor_hp';
            if (lbl.includes('pelanggan') || lbl.includes('meter')) return 'id_pelanggan';
            if (lbl.includes('bpjs') || lbl.includes('peserta')) return 'nomor_peserta';
            if (lbl.includes('username')) return 'username';
            return 'userid_only';
        }
        return 'custom';
    }
    const typePresetMap = {
        'game': 'userid_server', 'seluler': 'userid_server', 'pc': 'userid_only', 'voucher': 'voucher',
        'pulsa': 'nomor_hp', 'paket_data': 'nomor_hp', 'pln': 'id_pelanggan', 'pdam': 'id_pelanggan',
        'bpjs': 'nomor_peserta', 'internet': 'id_pelanggan', 'emoney': 'nomor_hp', 'ppob': 'id_pelanggan',
    };

    return {
        fields: existing.length > 0 ? existing.map(f => ({...f})) : [],
        selectedPreset: detectPreset(existing),
        applyPreset() {
            if (this.selectedPreset === 'custom') return;
            this.fields = presets[this.selectedPreset] ? presets[this.selectedPreset].map(f => ({...f})) : [];
        },
        handleTypeChange(type) {
            const preset = typePresetMap[type];
            if (preset) {
                this.selectedPreset = preset;
                this.applyPreset();
            }
        },
        addField() {
            this.fields.push({ name: 'target', label: '', placeholder: '', required: true });
        },
        removeField(index) {
            this.fields.splice(index, 1);
        }
    };
}
</script>
@endsection

