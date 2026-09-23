<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use Domain\Post\Actions\RenderPostContentAction;
use Domain\Post\Resources\PublishedPostResource;
use Inertia\Inertia;
use Inertia\Response;

class BlogPreviewController extends Controller
{
    /**
     * A post opened through its public preview link, whatever its status.
     * It is not counted as a view and is kept out of search engines.
     */
    public function show(string $token, RenderPostContentAction $renderPostContent): Response
    {
        $post = Post::query()
            ->where('preview_token', $token)
            ->with('tags')
            ->firstOrFail();

        return Inertia::render('blog/Show', [
            'post' => PublishedPostResource::make($post),
            'content' => $renderPostContent($post),
            'previous' => null,
            'next' => null,
            'related' => [],
            'preview' => [
                'editUrl' => null,
            ],
        ]);
    }
}
