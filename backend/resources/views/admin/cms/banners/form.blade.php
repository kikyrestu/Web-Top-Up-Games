<x-layouts.app :title="($formMode === 'create' ? 'Tambah Banner' : 'Edit Banner')">
    <div class="grid" style="max-width: 860px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah Banner' : 'Edit Banner' }}</h1>
            <form method="post" enctype="multipart/form-data" action="{{ $formMode === 'create' ? route('admin.cms.banners.store') : route('admin.cms.banners.update', ['banner' => $banner->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                <div>
                    <label for="title">Judul</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $banner->title) }}" required>
                </div>

                <div class="grid" style="grid-template-columns:1fr 2fr;">
                    <div>
                        <label for="position">Position</label>
                        <input id="position" name="position" type="text" value="{{ old('position', $banner->position ?: 'HERO') }}" required>
                    </div>
                    <div>
                        <label for="target_url">Target URL</label>
                        <input id="target_url" name="target_url" type="text" value="{{ old('target_url', $banner->target_url) }}" placeholder="https://...">
                    </div>
                </div>

                <div>
                    <label for="image_path">Image Path</label>
                    <input id="image_path" name="image_path" type="text" value="{{ old('image_path', $banner->image_path) }}" placeholder="/uploads/banner/hero-01.jpg">
                    <div class="muted" style="margin-top:6px; font-size:13px;">Boleh isi path manual atau upload file di bawah.</div>
                </div>

                <div>
                    <label for="uploaded_image">Upload Banner Image</label>
                    <input id="uploaded_image" name="uploaded_image" type="file" accept=".jpg,.jpeg,.png,.webp">
                    @if (!empty($banner->image_path))
                        <div class="muted" style="margin-top:6px; font-size:13px;">Current: {{ $banner->image_path }}</div>
                    @endif
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr 140px; align-items:end;">
                    <div>
                        <label for="start_at">Start At</label>
                        <input id="start_at" name="start_at" type="datetime-local" value="{{ old('start_at', $banner->start_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div>
                        <label for="end_at">End At</label>
                        <input id="end_at" name="end_at" type="datetime-local" value="{{ old('end_at', $banner->end_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div>
                        <label for="sort_order">Sort Order</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', (int) ($banner->sort_order ?? 0)) }}">
                    </div>
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $banner->is_active ?? true))>
                    Aktif
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.cms.banners.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
