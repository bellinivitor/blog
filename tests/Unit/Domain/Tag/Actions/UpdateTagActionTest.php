<?php

use App\Models\Tag\Tag;
use Domain\Tag\Actions\UpdateTagAction;
use Domain\Tag\DataTransferObjects\TagDTO;

it('regenerates the slug from the new name when none is given', function () {
    Tag::factory()->create(['slug' => 'vuejs']);
    $tag = Tag::factory()->create(['name' => 'Vue', 'slug' => 'vue']);

    $result = app(UpdateTagAction::class)($tag, TagDTO::fromArray(['name' => 'VueJS']));

    expect($result->refresh())
        ->name->toBe('VueJS')
        ->slug->toBe('vuejs-2');
})->group('Unit', 'Tag');
