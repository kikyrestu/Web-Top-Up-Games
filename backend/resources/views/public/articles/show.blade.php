<x-layouts.app :title="$article->title">
    <div class="grid">
        <div class="panel">
            <h1>{{ $article->title }}</h1>
            <div class="muted">Publish: {{ $article->published_at ?: $article->created_at }}</div>
            <div style="margin-top:12px; line-height:1.65;">
                {!! nl2br(e((string) $article->content)) !!}
            </div>
        </div>

        <div class="panel">
            <h2>Artikel Lainnya</h2>
            @forelse ($latestArticles as $item)
                <div style="border-bottom:1px solid var(--line); padding:10px 0;">
                    <a href="{{ route('public.articles.show', ['slug' => $item->slug]) }}"><strong>{{ $item->title }}</strong></a>
                    <div class="muted">{{ $item->published_at ?: $item->created_at }}</div>
                </div>
            @empty
                <p class="muted">Tidak ada artikel lain.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
