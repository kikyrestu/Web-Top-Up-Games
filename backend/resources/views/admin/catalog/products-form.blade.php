<x-layouts.app :title="($formMode === 'create' ? 'Tambah Product' : 'Edit Product')">
    <div class="grid" style="max-width: 860px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah Product' : 'Edit Product' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.catalog.products.store') : route('admin.catalog.products.update', ['product' => $row->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                <div>
                    <label for="name">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $row->name) }}" required>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="category_id">Category</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Pilih category</option>
                            @foreach ($categories as $category)
                                <option value="{{ (int) $category->id }}" @selected((int) old('category_id', $row->category_id) === (int) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="type">Type</label>
                        <select id="type" name="type" required>
                            @foreach (['TOPUP', 'PPOB'] as $type)
                                <option value="{{ $type }}" @selected(old('type', strtoupper((string) ($row->type ?: 'TOPUP'))) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="sku">SKU</label>
                        <input id="sku" name="sku" type="text" value="{{ old('sku', $row->sku) }}" required>
                    </div>
                    <div>
                        <label for="slug">Slug</label>
                        <input id="slug" name="slug" type="text" value="{{ old('slug', $row->slug) }}" placeholder="opsional, auto dari nama">
                    </div>
                </div>

                <div>
                    <label for="meta_json">Meta JSON (opsional)</label>
                    <textarea id="meta_json" name="meta_json" rows="8" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;">{{ old('meta_json', !empty($row->meta) ? json_encode($row->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $row->is_active ?? true))>
                    Aktif
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.catalog.products.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
