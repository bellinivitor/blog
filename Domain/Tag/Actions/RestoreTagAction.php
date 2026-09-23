<?php

namespace Domain\Tag\Actions;

use App\Models\Tag\Tag;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class RestoreTagAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(Tag $tag): void
    {
        try {
            DB::beginTransaction();

            $tag->restore();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }
    }
}
