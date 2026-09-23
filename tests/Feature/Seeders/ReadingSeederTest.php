<?php

use App\Models\Reading\Reading;
use Database\Seeders\ReadingSeeder;

test('seeds example readings with valid links, the last one newest', function () {
    $this->seed(ReadingSeeder::class);

    $readings = Reading::query()->latestFirst()->get();

    expect($readings)->toHaveCount(8)
        ->and($readings->first()->title)->toBe('Laravel Beyond CRUD')
        ->and($readings->pluck('url')->every(fn (string $url): bool => str_starts_with($url, 'https://')))->toBeTrue();
});

test('can run again without duplicating readings, bringing back trashed ones', function () {
    $this->seed(ReadingSeeder::class);
    Reading::query()->first()->delete();

    $this->seed(ReadingSeeder::class);

    expect(Reading::query()->withTrashed()->count())->toBe(8)
        ->and(Reading::query()->count())->toBe(8);
});
