<x-layouts.app :title="($formMode === 'create' ? 'Tambah Category' : 'Edit Category')">
    <div class="grid" style="max-width: 860px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah Category' : 'Edit Category' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.catalog.categories.store') : route('admin.catalog.categories.update', ['category' => $row->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                <div>
                    <label for="name">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $row->name) }}" required>
                </div>

                <div class="grid" style="grid-template-columns:2fr 1fr;">
                    <div>
                        <label for="slug">Slug</label>
                        <input id="slug" name="slug" type="text" value="{{ old('slug', $row->slug) }}" placeholder="opsional, auto dari nama">
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

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $row->is_active ?? true))>
                    Aktif
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.catalog.categories.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
