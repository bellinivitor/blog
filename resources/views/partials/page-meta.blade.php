{{-- Server-rendered meta for crawlers and link previews, which do not run JavaScript. --}}
<meta name="description" content="{{ $meta->description }}">
<link rel="canonical" href="{{ $meta->url }}">

<meta property="og:site_name" content="{{ config('blog.author') }}">
<meta property="og:locale" content="pt_BR">
<meta property="og:type" content="{{ $meta->type }}">
<meta property="og:title" content="{{ $meta->title }}">
<meta property="og:description" content="{{ $meta->description }}">
<meta property="og:url" content="{{ $meta->url }}">
@if ($meta->type === 'article')
    <meta property="article:author" content="{{ config('blog.author') }}">
    @if ($meta->publishedAt)
        <meta property="article:published_time" content="{{ $meta->publishedAt->toAtomString() }}">
    @endif
    @if ($meta->modifiedAt)
        <meta property="article:modified_time" content="{{ $meta->modifiedAt->toAtomString() }}">
    @endif
    @foreach ($meta->tags as $tag)
        <meta property="article:tag" content="{{ $tag }}">
    @endforeach
@endif

<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $meta->title }}">
<meta name="twitter:description" content="{{ $meta->description }}">
