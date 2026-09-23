<?php

namespace Domain\Reading\Actions;

use App\Models\Reading\Reading;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class DeleteReadingAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(Reading $reading): void
    {
        try {
            DB::beginTransaction();

            $reading->delete();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }
    }
}
