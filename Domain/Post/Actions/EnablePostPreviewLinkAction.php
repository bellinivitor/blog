<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

readonly class EnablePostPreviewLinkAction
{
    private const int TOKEN_LENGTH = 40;

    /**
     * Give the post a secret token so anyone with its preview link can read
     * it, whatever its status. A link that is already enabled keeps its token;
     * one enabled again after being disabled gets a new one, so old links
     * stay dead.
     *
     * @throws Throwable
     */
    public function __invoke(Post $post): Post
    {
        try {
            DB::beginTransaction();

            $post->preview_token ??= Str::random(self::TOKEN_LENGTH);
            $post->save();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }

        return $post;
    }
}
