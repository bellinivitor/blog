<?php

use App\Models\Tag\Tag;
use Domain\Shared\Actions\GenerateUniqueSlugAction;

it('slugifies the source when it is free', function () {
    $slug = app(GenerateUniqueSlugAction::class)('Laravel Cloud', Tag::class);

    expect($slug)->toBe('laravel-cloud');
})->group('Unit', 'Shared');

it('appends an incremental suffix when the slug is taken', function () {
    Tag::factory()->create(['slug' => 'vue']);
    Tag::factory()->create(['slug' => 'vue-2']);

    $slug = app(GenerateUniqueSlugAction::class)('Vue', Tag::class);

    expect($slug)->toBe('vue-3');
})->group('Unit', 'Shared');

it('treats slugs of trashed records as taken', function () {
    Tag::factory()->trashed()->create(['slug' => 'vue']);

    $slug = app(GenerateUniqueSlugAction::class)('Vue', Tag::class);

    expect($slug)->toBe('vue-2');
})->group('Unit', 'Shared');

it('ignores the record being updated', function () {
    $tag = Tag::factory()->create(['slug' => 'vue']);

    $slug = app(GenerateUniqueSlugAction::class)('Vue', Tag::class, $tag->id);

    expect($slug)->toBe('vue');
})->group('Unit', 'Shared');

it('falls back to a random slug when the source has no sluggable characters', function () {
    $slug = app(GenerateUniqueSlugAction::class)('!!!', Tag::class);

    expect($slug)->toMatch('/^[a-z0-9]{8}$/');
})->group('Unit', 'Shared');
