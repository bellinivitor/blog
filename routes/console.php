<?php

use Domain\Post\Actions\DiscardPostViewSaltAction;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(DiscardPostViewSaltAction::class)())
    ->name('discard-post-view-salt')
    ->dailyAt('00:05')
    ->timezone(config('blog.timezone'));
