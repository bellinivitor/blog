<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Illuminate\Support\Facades\DB;

readonly class LikePostAction
{
    /**
     * Add one like to a public post and return its new total. Readers are
     * anonymous, so the browser remembers it already liked the post; the
     * increment bypasses Eloquent so updated_at (and the caches keyed on it)
     * stay untouched.
     */
    public function __invoke(Post $post): int
    {
        return DB::transaction(function () use ($post): int {
            Post::query()->whereKey($post->id)->toBase()->increment('likes_count');

            return (int) Post::query()->whereKey($post->id)->value('likes_count');
        });
    }
}
