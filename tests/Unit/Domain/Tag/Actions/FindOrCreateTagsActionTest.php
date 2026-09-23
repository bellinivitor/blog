<?php

use App\Models\Tag\Tag;
use Domain\Tag\Actions\FindOrCreateTagsAction;

it('creates the tags that do not exist yet', function () {
    $ids = app(FindOrCreateTagsAction::class)(['Laravel Cloud', 'Pest']);

    expect(Tag::query()->whereKey($ids)->orderBy('name')->pluck('slug')->all())
        ->toBe(['laravel-cloud', 'pest']);
})->group('Unit', 'Tag');

it('reuses an active tag with the same name ignoring case and whitespace', function () {
    $tag = Tag::factory()->create(['name' => 'Laravel']);

    $ids = app(FindOrCreateTagsAction::class)(['  laravel ', 'LARAVEL']);

    expect($ids)->toBe([$tag->id]);
    $this->assertDatabaseCount('tags', 1);
})->group('Unit', 'Tag');

it('creates a new tag instead of reusing a trashed one', function () {
    $trashed = Tag::factory()->trashed()->create(['name' => 'Vue', 'slug' => 'vue']);

    $ids = app(FindOrCreateTagsAction::class)(['Vue']);

    expect($ids)->not->toContain($trashed->id);
    expect(Tag::query()->find($ids[0])->slug)->toBe('vue-2');
})->group('Unit', 'Tag');

it('ignores blank names', function () {
    $ids = app(FindOrCreateTagsAction::class)(['', '   ']);

    expect($ids)->toBe([]);
    $this->assertDatabaseCount('tags', 0);
})->group('Unit', 'Tag');
