<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use App\Models\Tag\Tag;
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
     * XML sitemap of the public blog: home, published posts and tags that
     * have at least one published post.
     */
    public function sitemap(): Response
    {
        $posts = Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->get(['slug', 'published_at', 'updated_at']);

        $tags = Tag::query()
            ->withPublishedPosts()
            ->orderBy('slug')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('blog.sitemap', [
                'posts' => $posts,
                'tags' => $tags,
                'lastPublishedAt' => $posts->max('updated_at'),
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
}
