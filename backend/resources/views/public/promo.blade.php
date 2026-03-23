<x-layouts.app :title="'Promo'">
    <div class="grid">
        <div class="panel">
            <h1>Promo & Campaign</h1>
            <p class="muted">Promo aktif dari banner dan halaman promo CMS.</p>
        </div>

        <div class="panel">
            <h2>Banner Aktif</h2>
            <table>
                <thead>
                <tr><th>Title</th><th>Position</th><th>Periode</th><th>Target URL</th></tr>
                </thead>
                <tbody>
                @forelse ($banners as $banner)
                    <tr>
                        <td>{{ $banner->title }}</td>
                        <td>{{ $banner->position }}</td>
                        <td>{{ $banner->start_at ?: '-' }} s/d {{ $banner->end_at ?: '-' }}</td>
                        <td>{{ $banner->target_url ?: '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="muted">Belum ada banner promo aktif.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Halaman Promo</h2>
            @forelse ($promoPages as $page)
                <div style="border-bottom:1px solid var(--line); padding:10px 0;">
                    <h3>{{ $page->title }}</h3>
                    <div class="muted">/{{ $page->slug }} • {{ $page->published_at ?: $page->created_at }}</div>
                    <div style="margin-top:6px;">{{ \Illuminate\Support\Str::limit(strip_tags((string) $page->content), 180) }}</div>
                </div>
            @empty
                <p class="muted">Belum ada halaman promo publish.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
