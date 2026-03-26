<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>{{ $global_site_name ?? 'PPOBKu' }}</title>
    <link>{{ url('/') }}</link>
    <description>{{ $global_site_description ?? 'Update artikel terbaru' }}</description>
    <language>id-ID</language>
    <atom:link href="{{ route('front.feed') }}" rel="self" type="application/rss+xml" />
    <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>

    @foreach($articles as $article)
    <item>
        <title><![CDATA[{{ $article->title }}]]></title>
        <link>{{ route('front.article.show', $article->slug) }}</link>
        <guid isPermaLink="true">{{ route('front.article.show', $article->slug) }}</guid>
        <pubDate>{{ optional($article->created_at)->toRssString() }}</pubDate>
        <description><![CDATA[{{ \Illuminate\Support\Str::limit(strip_tags($article->content), 300, '...') }}]]></description>
    </item>
    @endforeach
</channel>
</rss>
