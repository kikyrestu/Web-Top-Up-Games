<x-layouts.app :title="($formMode === 'create' ? 'Tambah Homepage Block' : 'Edit Homepage Block')">
    <div class="grid" style="max-width: 900px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah Homepage Block' : 'Edit Homepage Block' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.cms.homepage-blocks.store') : route('admin.cms.homepage-blocks.update', ['block' => $block->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="block_type">Block Type</label>
                        <select id="block_type" name="block_type" required>
                            @foreach ($blockTypes as $type)
                                <option value="{{ $type }}" @selected(old('block_type', strtoupper((string) ($block->block_type ?: 'HERO_SLIDE'))) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="section_key">Section Key</label>
                        <input id="section_key" name="section_key" type="text" value="{{ old('section_key', $block->section_key ?: 'MAIN') }}" required>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="title">Title</label>
                        <input id="title" name="title" type="text" value="{{ old('title', $block->title) }}" placeholder="Judul blok">
                    </div>
                    <div>
                        <label for="subtitle">Subtitle</label>
                        <input id="subtitle" name="subtitle" type="text" value="{{ old('subtitle', $block->subtitle) }}" placeholder="Subjudul blok">
                    </div>
                </div>

                <div>
                    <label for="body">Body</label>
                    <textarea id="body" name="body" rows="5" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px;">{{ old('body', $block->body) }}</textarea>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr;">
                    <div>
                        <label for="image_path">Image Path</label>
                        <input id="image_path" name="image_path" type="text" value="{{ old('image_path', $block->image_path) }}" placeholder="https://... atau /uploads/...">
                    </div>
                    <div>
                        <label for="target_url">Target URL</label>
                        <input id="target_url" name="target_url" type="text" value="{{ old('target_url', $block->target_url) }}" placeholder="https://... atau #quick-checkout">
                    </div>
                </div>

                <div>
                    <label for="payload_json">Payload JSON (opsional)</label>
                    <textarea id="payload_json" name="payload_json" rows="6" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px;" placeholder='{"class":"promo-1","links":["Item 1","Item 2"]}'>{{ old('payload_json', !empty($block->payload) ? json_encode($block->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
                </div>

                <div class="grid" style="grid-template-columns:1fr 1fr 140px; align-items:end;">
                    <div>
                        <label for="start_at">Start At</label>
                        <input id="start_at" name="start_at" type="datetime-local" value="{{ old('start_at', $block->start_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div>
                        <label for="end_at">End At</label>
                        <input id="end_at" name="end_at" type="datetime-local" value="{{ old('end_at', $block->end_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <div>
                        <label for="sort_order">Sort Order</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', (int) ($block->sort_order ?? 0)) }}">
                    </div>
                </div>

                <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $block->is_active ?? true))>
                    Aktif
                </label>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.cms.homepage-blocks.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
