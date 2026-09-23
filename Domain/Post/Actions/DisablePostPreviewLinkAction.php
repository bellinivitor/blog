<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class DisablePostPreviewLinkAction
{
    /**
     * Drop the post's preview token, so its public preview link stops working.
     *
     * @throws Throwable
     */
    public function __invoke(Post $post): Post
    {
        try {
            DB::beginTransaction();

            $post->preview_token = null;
            $post->save();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }

        return $post;
    }
}
