<x-layouts.app :title="'Admin CMS Pages'">
    <div class="grid">
        <div class="panel">
            <h1>CMS Pages</h1>
            <form method="get" action="{{ route('admin.cms.pages.index') }}" class="grid" style="grid-template-columns:2fr 1fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Judul / slug">
                </div>
                <div>
                    <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="">Semua</option>
                        @foreach (['PAGE', 'ARTICLE', 'PROMO'] as $type)
                            <option value="{{ $type }}" @selected($filters['type'] === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="btn" type="submit">Filter</button>
                </div>
                <div>
                    <a class="pill" href="{{ route('admin.cms.pages.create') }}">+ Tambah Page</a>
                </div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Judul</th>
                    <th>Slug</th>
                    <th>Type</th>
                    <th>Publish</th>
                    <th>Updated</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($pages as $page)
                    <tr>
                        <td>{{ $page->title }}</td>
                        <td>{{ $page->slug }}</td>
                        <td>{{ strtoupper((string) $page->type) }}</td>
                        <td>
                            @if ($page->is_published)
                                <span class="tag tag-pass">Published</span>
                            @else
                                <span class="tag tag-warn">Draft</span>
                            @endif
                        </td>
                        <td>{{ $page->updated_at }}</td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.cms.pages.edit', ['page' => $page->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.cms.pages.destroy', ['page' => $page->id]) }}" onsubmit="return confirm('Hapus page ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada data CMS page.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">
                {{ $pages->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
