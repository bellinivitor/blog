<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use Domain\Post\Actions\RenderPostContentAction;
use Illuminate\Http\Response;

class BlogFeedController extends Controller
{
    private const int MAX_ITEMS = 20;

    /**
     * RSS 2.0 feed with the latest published posts and their full content.
     */
    public function index(RenderPostContentAction $renderPostContent): Response
    {
        $posts = Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->limit(self::MAX_ITEMS)
            ->get();

        $items = $posts->map(fn (Post $post): array => [
            'post' => $post,
            'html' => $renderPostContent($post),
        ]);

        return response()
            ->view('blog.feed', [
                'items' => $items,
                'lastBuildDate' => $posts->map(fn (Post $post) => $post->revised_at ?? $post->published_at)->max() ?? now(),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
