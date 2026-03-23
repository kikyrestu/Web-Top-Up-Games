<x-layouts.app :title="'Artikel'">
    <div class="grid">
        <div class="panel">
            <h1>Artikel</h1>
            <p class="muted">Konten edukasi dan update promo/topup terbaru.</p>
        </div>

        <div class="panel">
            @forelse ($articles as $article)
                <div style="border-bottom:1px solid var(--line); padding:10px 0;">
                    <h3><a href="{{ route('public.articles.show', ['slug' => $article->slug]) }}">{{ $article->title }}</a></h3>
                    <div class="muted">{{ $article->published_at ?: $article->created_at }}</div>
                    <div style="margin-top:6px;">{{ \Illuminate\Support\Str::limit(strip_tags((string) $article->content), 200) }}</div>
                </div>
            @empty
                <p class="muted">Belum ada artikel dipublish.</p>
            @endforelse

            <div style="margin-top:12px;">
                {{ $articles->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
