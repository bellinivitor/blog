{{-- Split so the compiled PHP never contains a literal "?>" --}}
{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
@if ($lastPublishedAt)
        <lastmod>{{ $lastPublishedAt->toAtomString() }}</lastmod>
@endif
    </url>
    <url>
        <loc>{{ route('blog.readings.index') }}</loc>
@if ($lastReadingAt)
        <lastmod>{{ $lastReadingAt->toAtomString() }}</lastmod>
@endif
    </url>
@foreach ($posts as $post)
    <url>
        <loc>{{ route('blog.posts.show', $post->slug) }}</loc>
        <lastmod>{{ ($post->revised_at ?? $post->published_at)->toAtomString() }}</lastmod>
    </url>
@endforeach
@foreach ($tags as $tag)
    <url>
        <loc>{{ route('blog.tags.show', $tag->slug) }}</loc>
    </url>
@endforeach
</urlset>
