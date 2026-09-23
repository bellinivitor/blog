<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use Domain\Post\Actions\BuildPostPageMetaAction;
use Domain\Post\Actions\RenderPostContentAction;
use Domain\Post\Resources\PublishedPostResource;
use Domain\Shared\DataTransferObjects\PageMetaDTO;
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
        ])->withViewData(['meta' => new PageMetaDTO(
            title: config('blog.author'),
            description: config('blog.headline').' '.config('blog.bio'),
            url: route('home'),
        )]);
    }

    /**
     * A single published post.
     */
    public function show(
        string $slug,
        RenderPostContentAction $renderPostContent,
        BuildPostPageMetaAction $buildPageMeta,
    ): Response {
        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->with('tags')
            ->firstOrFail();

        return Inertia::render('blog/Show', [
            'post' => PublishedPostResource::make($post),
            'content' => $renderPostContent($post),
        ])->withViewData(['meta' => $buildPageMeta($post)]);
    }
}
