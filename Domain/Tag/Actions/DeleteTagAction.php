<?php

namespace Domain\Tag\Actions;

use App\Models\Tag\Tag;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class DeleteTagAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(Tag $tag): void
    {
        try {
            DB::beginTransaction();

            $tag->delete();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }
    }
}
