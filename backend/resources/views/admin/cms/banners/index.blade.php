<x-layouts.app :title="'Admin CMS Banners'">
    <div class="grid">
        <div class="panel">
            <h1>CMS Banners</h1>
            <form method="get" action="{{ route('admin.cms.banners.index') }}" class="grid" style="grid-template-columns:2fr 1fr auto auto; align-items:end; margin-top:12px;">
                <div>
                    <label for="q">Cari</label>
                    <input id="q" name="q" type="text" value="{{ $filters['q'] }}" placeholder="Judul banner">
                </div>
                <div>
                    <label for="position">Position</label>
                    <input id="position" name="position" type="text" value="{{ $filters['position'] }}" placeholder="HERO / SIDEBAR">
                </div>
                <div>
                    <button class="btn" type="submit">Filter</button>
                </div>
                <div>
                    <a class="pill" href="{{ route('admin.cms.banners.create') }}">+ Tambah Banner</a>
                </div>
            </form>
        </div>

        <div class="panel">
            <table>
                <thead>
                <tr>
                    <th>Judul</th>
                    <th>Posisi</th>
                    <th>Image Path</th>
                    <th>Window</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($banners as $banner)
                    <tr>
                        <td>{{ $banner->title }}</td>
                        <td>{{ strtoupper((string) $banner->position) }}</td>
                        <td>{{ $banner->image_path }}</td>
                        <td>
                            {{ $banner->start_at ?: '-' }}<br>
                            s/d {{ $banner->end_at ?: '-' }}
                        </td>
                        <td>{{ (int) $banner->sort_order }}</td>
                        <td>
                            @if ($banner->is_active)
                                <span class="tag tag-pass">Aktif</span>
                            @else
                                <span class="tag tag-warn">Nonaktif</span>
                            @endif
                        </td>
                        <td style="display:flex; gap:6px;">
                            <a class="pill" href="{{ route('admin.cms.banners.edit', ['banner' => $banner->id]) }}">Edit</a>
                            <form method="post" action="{{ route('admin.cms.banners.destroy', ['banner' => $banner->id]) }}" onsubmit="return confirm('Hapus banner ini?')">
                                @csrf
                                @method('delete')
                                <button class="pill" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Belum ada data banner.</td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px;">
                {{ $banners->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
