<x-layouts.app :title="($formMode === 'create' ? 'Tambah SEO Meta' : 'Edit SEO Meta')">
    <div class="grid" style="max-width: 920px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah SEO Meta' : 'Edit SEO Meta' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.seo.store') : route('admin.seo.update', ['seo' => $seo->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="entity_type">Entity Type</label>
                        <select id="entity_type" name="entity_type" required>
                            @foreach ($entityTypes as $type)
                                <option value="{{ $type }}" @selected(old('entity_type', strtoupper((string) ($seo->entity_type ?: $selectedEntityType))) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="entity_id">Entity ID</label>
                        <input id="entity_id" name="entity_id" type="number" min="1" value="{{ old('entity_id', (int) ($seo->entity_id ?: 0)) }}" required>
                    </div>
                </div>

                <div class="panel" style="padding:12px; background:#fbfcfb;">
                    <h3 style="margin-bottom:8px;">Referensi Entity (200 terakhir)</h3>
                    @foreach ($entityOptions as $type => $items)
                        <div style="margin-bottom:10px;">
                            <strong>{{ $type }}</strong>
                            <div class="muted" style="margin-top:4px; font-size:13px;">
                                @if (count($items) === 0)
                                    Tidak ada data.
                                @else
                                    @foreach (array_slice($items, 0, 8) as $item)
                                        #{{ $item['id'] }} {{ $item['label'] }}@if (!$loop->last), @endif
                                    @endforeach
                                    @if (count($items) > 8)
                                        ...
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div>
                    <label for="meta_title">Meta Title</label>
                    <input id="meta_title" name="meta_title" type="text" maxlength="255" value="{{ old('meta_title', $seo->meta_title) }}">
                </div>

                <div>
                    <label for="meta_description">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" rows="3" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#fff;">{{ old('meta_description', $seo->meta_description) }}</textarea>
                </div>

                <div>
                    <label for="meta_keywords">Meta Keywords</label>
                    <textarea id="meta_keywords" name="meta_keywords" rows="2" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#fff;">{{ old('meta_keywords', $seo->meta_keywords) }}</textarea>
                </div>

                <div>
                    <label for="og_title">OG Title</label>
                    <input id="og_title" name="og_title" type="text" maxlength="255" value="{{ old('og_title', $seo->og_title) }}">
                </div>

                <div>
                    <label for="og_description">OG Description</label>
                    <textarea id="og_description" name="og_description" rows="3" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#fff;">{{ old('og_description', $seo->og_description) }}</textarea>
                </div>

                <div>
                    <label for="og_image_path">OG Image Path</label>
                    <input id="og_image_path" name="og_image_path" type="text" maxlength="255" value="{{ old('og_image_path', $seo->og_image_path) }}" placeholder="/uploads/seo/og-image.jpg">
                </div>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.seo.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
