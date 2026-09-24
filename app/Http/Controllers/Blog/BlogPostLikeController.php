<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post\Post;
use Domain\Post\Actions\LikePostAction;
use Domain\Post\Resources\PostLikesResource;

class BlogPostLikeController extends Controller
{
    /**
     * A reader liked a published post.
     */
    public function store(string $slug, LikePostAction $likePost): PostLikesResource
    {
        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return PostLikesResource::make($likePost($post));
    }
}
