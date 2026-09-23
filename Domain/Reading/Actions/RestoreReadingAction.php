<?php

namespace Domain\Reading\Actions;

use App\Models\Reading\Reading;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class RestoreReadingAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(Reading $reading): void
    {
        try {
            DB::beginTransaction();

            $reading->restore();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }
    }
}
