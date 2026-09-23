<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Carbon\CarbonInterface;
use Domain\Post\Enums\PostStatus;
use Domain\Post\Interfaces\PostStatusState;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ChangePostStatusAction
{
    /**
     * Apply the status transition. Publishing uses $publishAt when given (a
     * future date schedules the post, a past one backdates it). Otherwise the
     * first publication stamps now, a leftover future date from a cancelled
     * schedule is replaced by now, and an earlier publication date is kept.
     *
     * @throws Throwable
     */
    public function __invoke(Post $post, PostStatusState $state, ?CarbonInterface $publishAt = null): Post
    {
        try {
            DB::beginTransaction();

            $post->status = $state->handle();

            if ($post->status === PostStatus::Published) {
                $post->published_at = match (true) {
                    $publishAt !== null => $publishAt->toImmutable(),
                    $post->published_at === null, $post->published_at->isFuture() => now(),
                    default => $post->published_at,
                };
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
