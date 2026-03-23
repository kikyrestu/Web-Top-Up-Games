<x-layouts.app :title="'Admin SEO Manager'">
    <div class="grid">
        <div class="panel">
            <h1>SEO Manager</h1>
            <form method="get" action="{{ route('admin.seo.index') }}" class="grid" style="grid-template-columns:2fr 1fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="meta title / og title / entity id">
                </div>
                <div>
                    <label for="entity_type">Entity Type</label>
                    <select id="entity_type" name="entity_type">
                        <option value="">Semua</option>
                        @foreach ($entityTypes as $type)
                            <option value="{{ $type }}" @selected($filters['entity_type'] === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button class="btn" type="submit">Filter</button>
                </div>
                <div>
                    <a class="pill" href="{{ route('admin.seo.create') }}">+ Tambah SEO</a>
                </div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Entity</th>
                    <th>Meta Title</th>
                    <th>Meta Description</th>
                    <th>OG Title</th>
                    <th>Updated</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->entity_type }} #{{ (int) $row->entity_id }}</td>
                        <td>{{ $row->meta_title ?: '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit((string) ($row->meta_description ?: '-'), 120) }}</td>
                        <td>{{ $row->og_title ?: '-' }}</td>
                        <td>{{ $row->updated_at }}</td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.seo.edit', ['seo' => $row->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.seo.destroy', ['seo' => $row->id]) }}" onsubmit="return confirm('Hapus SEO meta ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Belum ada data SEO meta.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">{{ $rows->links() }}</div>
        </div>
    </div>
</x-layouts.app>
