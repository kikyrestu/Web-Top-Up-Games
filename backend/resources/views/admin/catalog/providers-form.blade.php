<x-layouts.app :title="($formMode === 'create' ? 'Tambah Provider' : 'Edit Provider')">
    <div class="grid" style="max-width: 860px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah Provider' : 'Edit Provider' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.catalog.providers.store') : route('admin.catalog.providers.update', ['provider' => $row->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="code">Code</label>
                        <input id="code" name="code" type="text" value="{{ old('code', $row->code) }}" required>
                    </div>
                    <div>
                        <label for="name">Nama</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $row->name) }}" required>
                    </div>
                </div>

                <div>
                    <label for="settings_json">Settings JSON (opsional)</label>
                    <textarea id="settings_json" name="settings_json" rows="10" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#0d1d38; color:#e8f0ff;">{{ old('settings_json', !empty($row->settings) ? json_encode($row->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $row->is_active ?? true))>
                    Aktif
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.catalog.providers.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
