<?php

namespace Domain\Reading\Actions;

use App\Models\Reading\Reading;
use Domain\Reading\DataTransferObjects\ReadingDTO;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class StoreReadingAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(ReadingDTO $readingDTO): Reading
    {
        try {
            DB::beginTransaction();

            $reading = new Reading;
            $reading->fill($readingDTO->toArray());
            $reading->save();

            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollBack();

            throw $throwable;
        }

        return $reading;
    }
}
