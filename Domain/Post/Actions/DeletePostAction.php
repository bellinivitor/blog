<?php

namespace Domain\Post\Actions;

use App\Models\Post\Post;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class DeletePostAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(Post $post): void
    {
        try {
            DB::beginTransaction();

            $post->delete();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }
    }
}
