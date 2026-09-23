<?php

namespace Domain\Post\Actions;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

readonly class DiscardPostViewSaltAction
{
    /**
     * Delete yesterday's visitor salt so the day's hashes can never be tied
     * back to an IP. It has already expired, but expired entries of the
     * database cache stay in the table until something removes them.
     */
    public function __invoke(): void
    {
        $yesterday = CarbonImmutable::now(config('blog.timezone'))->subDay()->toDateString();

        Cache::forget(RecordPostViewAction::saltKey($yesterday));
    }
}
