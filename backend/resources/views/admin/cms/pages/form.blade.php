<x-layouts.app :title="($formMode === 'create' ? 'Tambah CMS Page' : 'Edit CMS Page')">
    <div class="grid" style="max-width: 860px;">
        <div class="panel">
            <h1>{{ $formMode === 'create' ? 'Tambah CMS Page' : 'Edit CMS Page' }}</h1>
            <form method="post" action="{{ $formMode === 'create' ? route('admin.cms.pages.store') : route('admin.cms.pages.update', ['page' => $page->id]) }}" class="grid" style="margin-top:12px;">
                @csrf
                @if ($formMode === 'edit')
                    @method('put')
                @endif

                <div>
                    <label for="title">Judul</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $page->title) }}" required>
                </div>

                <div class="grid" style="grid-template-columns:2fr 1fr;">
                    <div>
                        <label for="slug">Slug</label>
                        <input id="slug" name="slug" type="text" value="{{ old('slug', $page->slug) }}" placeholder="opsional, auto-generate dari judul">
                    </div>
                    <div>
                        <label for="type">Type</label>
                        <select id="type" name="type" required>
                            @foreach (['PAGE', 'ARTICLE', 'PROMO'] as $type)
                                <option value="{{ $type }}" @selected(old('type', strtoupper((string) ($page->type ?: 'PAGE'))) === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="content">Content</label>
                    <textarea id="content" name="content" rows="14" style="width:100%; border:1px solid var(--line); border-radius:10px; padding:10px 11px; font-size:14px; background:#fff;">{{ old('content', $page->content) }}</textarea>
                </div>

                <div class="grid" style="grid-template-columns:1fr auto; align-items:end;">
                    <div>
                        <label for="published_at">Published At</label>
                        <input id="published_at" name="published_at" type="datetime-local" value="{{ old('published_at', $page->published_at?->format('Y-m-d\TH:i')) }}">
                    </div>
                    <label style="display:flex; align-items:center; gap:8px; font-weight:700;">
                        <input type="checkbox" name="is_published" value="1" @checked((bool) old('is_published', $page->is_published))>
                        Publish
                    </label>
                </div>

                <div style="display:flex; gap:10px;">
                    <button class="btn" type="submit">Simpan</button>
                    <a class="pill" href="{{ route('admin.cms.pages.index') }}">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
