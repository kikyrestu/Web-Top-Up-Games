@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('header', 'Edit Produk')

@section('content')
<div class="flex justify-center w-full"><div class="w-full max-w-3xl"><div class="glass-panel rounded-2xl shadow-xl border border-dark-700 p-6 sm:p-8 mb-8 relative overflow-hidden w-full">
    <!-- Decorative background element -->
    <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="mb-6 border-l-4 border-brand-500 pl-3">
        <h2 class="text-lg font-bold text-white tracking-tight">Edit Data Produk</h2>
        <p class="text-xs text-gray-500 mt-1">Ubah informasi produk di bawah ini sesuai kebutuhan.</p>
    </div>

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="relative z-10">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="name" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Nama Produk <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center w-10 justify-center pointer-events-none">
                        <i class="fas fa-box text-gray-500"></i>
                    </div>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" class="w-full pl-10 pr-4 py-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-inner" required>
                </div>
                @error('name')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="category_id" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Kategori <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center w-10 justify-center pointer-events-none">
                        <i class="fas fa-layer-group text-gray-500"></i>
                    </div>
                    <select name="category_id" id="category_id" class="w-full pl-10 pr-10 py-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-inner appearance-none" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
                @error('category_id')<p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>
        </div>

        @php
            $existingMappings = $product->providerMappings
                ->map(function ($mapping) {
                    return [
                        'api_provider_id' => $mapping->api_provider_id,
                        'provider_product_code' => $mapping->provider_product_code,
                        'price_capital' => $mapping->price_capital,
                        'priority' => $mapping->priority,
                    ];
                })
                ->values()
                ->all();

            $defaultMappings = array_pad($existingMappings, 3, ['api_provider_id' => '', 'provider_product_code' => '', 'price_capital' => '', 'priority' => '']);
            $oldMappings = old('provider_mappings', $defaultMappings);
        @endphp
        <div class="mb-5 bg-dark-800/50 p-4 rounded-xl border border-dark-700">
            <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-3">Mapping Provider (Multi Provider)</label>
            <div class="space-y-3">
                @foreach($oldMappings as $index => $mapping)
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-dark-900/70 border border-dark-700 rounded-xl p-3">
                        <div class="md:col-span-4">
                            <label class="block text-[11px] text-gray-500 mb-1">Provider</label>
                            <select name="provider_mappings[{{ $index }}][api_provider_id]" class="w-full bg-dark-800 border border-dark-600 text-white text-sm rounded-lg p-2.5">
                                <option value="">-- Pilih Provider --</option>
                                @foreach($providers as $provider)
                                    <option value="{{ $provider->id }}" {{ (string) ($mapping['api_provider_id'] ?? '') === (string) $provider->id ? 'selected' : '' }}>{{ $provider->name }} ({{ strtoupper($provider->code) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[11px] text-gray-500 mb-1">Kode Produk Provider</label>
                            <input type="text" name="provider_mappings[{{ $index }}][provider_product_code]" value="{{ $mapping['provider_product_code'] ?? '' }}" class="w-full bg-dark-800 border border-dark-600 text-white rounded-lg p-2.5 font-mono text-sm" placeholder="contoh: TSEL10">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] text-gray-500 mb-1">Modal Provider</label>
                            <input type="number" step="0.01" min="0" name="provider_mappings[{{ $index }}][price_capital]" value="{{ $mapping['price_capital'] ?? '' }}" class="w-full bg-dark-800 border border-dark-600 text-white rounded-lg p-2.5 text-sm" placeholder="0">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[11px] text-gray-500 mb-1">Prioritas</label>
                            <input type="number" min="0" max="999" name="provider_mappings[{{ $index }}][priority]" value="{{ $mapping['priority'] ?? $index }}" class="w-full bg-dark-800 border border-dark-600 text-white rounded-lg p-2.5 text-sm" placeholder="{{ $index }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
            <div>
                <label for="price_capital" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Harga Modal <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center w-12 justify-center pointer-events-none border-r border-dark-600">
                        <span class="text-gray-500 font-bold text-sm">Rp</span>
                    </div>
                    <input type="number" name="price_capital" id="price_capital" value="{{ old('price_capital', $product->price_capital) }}" class="w-full pl-14 pr-4 py-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-inner font-mono" required>
                </div>
            </div>

            <div>
                <label for="price_sell" class="block text-brand-400 text-xs font-bold uppercase tracking-wider mb-2">Harga Jual <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center w-12 justify-center pointer-events-none border-r border-brand-500/30">
                        <span class="text-brand-400 font-bold text-sm">Rp</span>
                    </div>
                    <input type="number" name="price_sell" id="price_sell" value="{{ old('price_sell', $product->price_sell) }}" class="w-full pl-14 pr-4 py-2.5 bg-brand-500/5 border border-brand-500/30 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-inner font-mono ring-1 ring-brand-500/10" required>
                </div>
            </div>
        </div>

        <div class="mb-5">
            <label for="description" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Deskripsi Produk</label>
            <textarea name="description" id="description" rows="3" class="w-full p-3 bg-dark-800 border border-dark-600 text-white rounded-xl focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-all shadow-inner">{{ old('description', $product->description) }}</textarea>
        </div>

        <!-- Override Keuntungan per Produk -->
        <div class="mb-5 bg-dark-800/30 p-4 rounded-xl border border-dark-700">
            <label class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-1"><i class="fas fa-coins mr-1 text-brand-400"></i> Override Keuntungan (Opsional)</label>
            <p class="text-gray-500 text-[11px] mb-3">Kosongkan = ikut setting kategori atau global. Hanya berlaku untuk produk prabayar.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-500 text-xs mb-1">Tipe Keuntungan</label>
                    <select name="commission_type" class="w-full p-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                        <option value="" {{ empty(old('commission_type', $product->commission_type)) ? 'selected' : '' }}>-- Ikut Kategori/Global --</option>
                        <option value="percentage" {{ old('commission_type', $product->commission_type) === 'percentage' ? 'selected' : '' }}>Persentase (%)</option>
                        <option value="flat" {{ old('commission_type', $product->commission_type) === 'flat' ? 'selected' : '' }}>Nominal Tetap (Rp)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-500 text-xs mb-1">Nilai Keuntungan</label>
                    <input type="number" step="0.01" name="commission_value" value="{{ old('commission_value', $product->commission_value) }}" placeholder="Contoh: 5 untuk 5% atau 500" class="w-full p-2.5 bg-dark-800 border border-dark-600 text-white rounded-xl text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <div class="mb-6 bg-dark-800/30 p-5 rounded-xl border border-dark-700 flex flex-col gap-6">
            <div>
                <label for="image" class="block text-gray-400 text-xs font-bold uppercase tracking-wider mb-2">Gambar / Icon Produk</label>
                <div class="flex items-center gap-4">
                    @if($product->image)
                        <div class="relative group h-16 w-16 shrink-0">
                            <img src="{{ asset('storage/' . $product->image) }}" class="h-16 w-16 object-cover rounded-xl border border-dark-600 shadow-md transition-transform group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/50 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                                <i class="fas fa-image text-white"></i>
                            </div>
                        </div>
                    @else
                        <div class="w-16 h-16 shrink-0 rounded-xl bg-dark-700 border border-dark-600 shadow-inner flex items-center justify-center text-gray-500">
                            <i class="fas fa-image text-xl"></i>
                        </div>
                    @endif
                    <div class="flex-1">
                        <p class="text-[11px] text-gray-500 mb-2">Upload file baru untuk mengganti gambar saat ini. Biarkan kosong jika tidak ingin mengubah.</p>
                        <input type="file" name="image" id="image" class="w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 file:transition-colors cursor-pointer" accept="image/*">
                    </div>
                </div>
                @error('image')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </div>

            <div class="pt-4 border-t border-dark-700/50 flex justify-between items-center">
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-dark-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-brand-500/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-400 peer-checked:after:bg-white after:border-gray-500 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 border border-dark-600"></div>
                    <span class="ml-3 text-sm font-bold text-gray-300 group-hover:text-white transition-colors">Produk Aktif</span>
                </label>
            </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6 pt-5 border-t border-dark-700">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 bg-dark-800 rounded-xl text-sm font-bold text-gray-400 hover:text-white hover:bg-dark-700 border border-dark-600 hover:border-gray-500 transition-all shadow-sm">Batal</a>
            <button type="submit" class="bg-gradient-to-r from-brand-500 to-brand-400 hover:from-brand-400 hover:to-brand-500 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg shadow-brand-500/30 transition-all duration-300 transform hover:-translate-y-0.5 flex items-center gap-2">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
</div>
</div>
@endsection
