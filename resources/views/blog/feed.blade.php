{{-- Split so the compiled PHP never contains a literal "?>" --}}
{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ config('blog.author') }}</title>
        <link>{{ route('home') }}</link>
        <description>{{ config('blog.headline') }}</description>
        <language>pt-BR</language>
        <lastBuildDate>{{ $lastBuildDate->toRssString() }}</lastBuildDate>
        <atom:link href="{{ route('blog.feed') }}" rel="self" type="application/rss+xml" />
@foreach ($items as ['post' => $post, 'html' => $html])
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ route('blog.posts.show', $post->slug) }}</link>
            <guid isPermaLink="true">{{ route('blog.posts.show', $post->slug) }}</guid>
            <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
@if ($post->revised_at)
            <atom:updated>{{ $post->revised_at->toAtomString() }}</atom:updated>
@endif
@if ($post->excerpt)
            <description>{{ $post->excerpt }}</description>
@endif
            <content:encoded>{{ $html }}</content:encoded>
        </item>
@endforeach
    </channel>
</rss>
