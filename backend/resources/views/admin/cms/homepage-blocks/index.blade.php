<x-layouts.app :title="'Admin Homepage Blocks'">
    <div class="grid">
        <div class="panel">
            <h1>Homepage Blocks</h1>
            <form method="get" action="{{ route('admin.cms.homepage-blocks.index') }}" class="grid" style="grid-template-columns:2fr 1fr 1fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Judul / subtitle">
                </div>
                <div>
                    <label for="block_type">Block Type</label>
                    <select id="block_type" name="block_type">
                        <option value="">Semua</option>
                        @foreach ($blockTypes as $type)
                            <option value="{{ $type }}" @selected($filters['block_type'] === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="section_key">Section</label>
                    <input id="section_key" name="section_key" type="text" value="{{ $filters['section_key'] }}" placeholder="MAIN">
                </div>
                <div>
                    <button class="btn" type="submit">Filter</button>
                </div>
                <div>
                    <a class="pill" href="{{ route('admin.cms.homepage-blocks.create') }}">+ Tambah Block</a>
                </div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Type</th>
                    <th>Section</th>
                    <th>Title</th>
                    <th>Sort</th>
                    <th>Window</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($blocks as $block)
                    <tr>
                        <td>{{ strtoupper((string) $block->block_type) }}</td>
                        <td>{{ strtoupper((string) $block->section_key) }}</td>
                        <td>{{ $block->title ?: '-' }}</td>
                        <td>{{ (int) $block->sort_order }}</td>
                        <td>
                            {{ $block->start_at ?: '-' }}<br>
                            s/d {{ $block->end_at ?: '-' }}
                        </td>
                        <td>
                            @if ($block->is_active)
                                <span class="tag tag-pass">Aktif</span>
                            @else
                                <span class="tag tag-warn">Nonaktif</span>
                            @endif
                        </td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.cms.homepage-blocks.edit', ['block' => $block->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.cms.homepage-blocks.destroy', ['block' => $block->id]) }}" onsubmit="return confirm('Hapus block ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Belum ada block homepage.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">
                {{ $blocks->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
