<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Domain\Post\Enums\PostStatus;
use Domain\Post\Interfaces\PostStatusState;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ChangePostStatusAction
{
    /**
     * Apply the status transition. The first publication stamps published_at,
     * which is kept when the post is unpublished and published again.
     *
     * @throws Throwable
     */
    public function __invoke(Post $post, PostStatusState $state): Post
    {
        try {
            DB::beginTransaction();

            $post->status = $state->handle();

            if ($post->status === PostStatus::Published && $post->published_at === null) {
                $post->published_at = now();
            }

            $post->save();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }

        return $post;
    }
}
