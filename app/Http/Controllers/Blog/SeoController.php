<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use App\Models\Reading\Reading;
use App\Models\Tag\Tag;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Admin and account areas that crawlers should skip.
     *
     * @var array<int, string>
     */
    private const array PRIVATE_PATHS = ['/admin'];

    /**
     * XML sitemap of the public blog: home, readings, published posts and
     * tags that have at least one published post.
     */
    public function sitemap(): Response
    {
        $posts = Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->get(['slug', 'published_at', 'revised_at']);

        $tags = Tag::query()
            ->withPublishedPosts()
            ->orderBy('slug')
            ->get(['slug', 'updated_at']);

        $lastReadingAt = Reading::query()->max('updated_at');

        return response()
            ->view('blog.sitemap', [
                'posts' => $posts,
                'tags' => $tags,
                'lastPublishedAt' => $posts->max('published_at'),
                'lastReadingAt' => $lastReadingAt !== null ? CarbonImmutable::parse($lastReadingAt) : null,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * robots.txt pointing to the sitemap and keeping crawlers out of the admin.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            ...array_map(fn (string $path): string => "Disallow: {$path}", self::PRIVATE_PATHS),
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * security.txt (RFC 9116) telling researchers where to report issues.
     * Expires is always a year ahead so the file never goes stale.
     */
    public function security(): Response
    {
        $lines = [
            'Contact: '.config('blog.security_contact'),
            'Expires: '.now()->addYear()->startOfDay()->toIso8601ZuluString(),
            'Preferred-Languages: pt, en',
            'Canonical: '.route('security'),
        ];

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * llms.txt: a Markdown index of the blog for language models, listing
     * every published post with its excerpt.
     */
    public function llms(): Response
    {
        $posts = Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->get(['slug', 'title', 'excerpt']);

        $postLines = $posts->map(function (Post $post): string {
            $line = "- [{$post->title}](".route('blog.posts.show', $post->slug).')';

            return filled($post->excerpt) ? "{$line}: {$post->excerpt}" : $line;
        });

        $lines = [
            '# '.config('blog.author'),
            '',
            '> '.config('blog.headline'),
            '',
            config('blog.bio'),
            '',
            '## Posts',
            '',
            ...$postLines,
            '',
            '## Optional',
            '',
            '- [Leituras]('.route('blog.readings.index').')',
            '- [Feed RSS]('.route('blog.feed').'): os posts mais recentes com o conteúdo completo',
        ];

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/markdown; charset=UTF-8');
    }
}
