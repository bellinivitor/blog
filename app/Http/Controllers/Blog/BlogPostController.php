<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use Domain\Post\Actions\RenderPostContentAction;
use Domain\Post\Resources\PublishedPostResource;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostController extends Controller
{
    /**
     * The blog home: intro and every published post, newest first.
     */
    public function index(): Response
    {
        $posts = Post::query()
            ->published()
            ->with('tags')
            ->orderByDesc('published_at')
            ->get();

        return Inertia::render('blog/Index', [
            'posts' => PublishedPostResource::collection($posts),
        ]);
    }

    /**
     * A single published post.
     */
    public function show(string $slug, RenderPostContentAction $renderPostContent): Response
    {
        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->with('tags')
            ->firstOrFail();

        return Inertia::render('blog/Show', [
            'post' => PublishedPostResource::make($post),
            'content' => $renderPostContent($post),
        ]);
    }
}
