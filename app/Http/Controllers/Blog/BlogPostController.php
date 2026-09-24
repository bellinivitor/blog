<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use Domain\Post\Actions\BuildPostPageMetaAction;
use Domain\Post\Actions\FindRelatedPostsAction;
use Domain\Post\Actions\RecordPostViewAction;
use Domain\Post\Actions\RenderPostContentAction;
use Domain\Post\DataTransferObjects\PostViewDTO;
use Domain\Post\Resources\PublishedPostResource;
use Domain\Shared\DataTransferObjects\PageMetaDTO;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogPostController extends Controller
{
    private const int RELATED_LIMIT = 3;

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
        Request $request,
        string $slug,
        RenderPostContentAction $renderPostContent,
        BuildPostPageMetaAction $buildPageMeta,
        FindRelatedPostsAction $findRelatedPosts,
        RecordPostViewAction $recordPostView,
    ): Response {
        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->with('tags')
            ->firstOrFail();

        $recordPostView($post, PostViewDTO::fromHttpRequest($request));

        $previous = Post::query()->publishedBefore($post)->with('tags')->first();
        $next = Post::query()->publishedAfter($post)->with('tags')->first();
        $related = $findRelatedPosts($post, self::RELATED_LIMIT);

        return Inertia::render('blog/Show', [
            'post' => PublishedPostResource::make($post),
            'content' => $renderPostContent($post),
            'previous' => $previous ? PublishedPostResource::make($previous) : null,
            'next' => $next ? PublishedPostResource::make($next) : null,
            'related' => PublishedPostResource::collection($related),
            'likes' => $post->likes_count,
        ])->withViewData(['meta' => $buildPageMeta($post)]);
    }
}
