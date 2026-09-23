<?php

use App\Models\Post\Post;
use Domain\Post\Actions\ChangePostStatusAction;
use Domain\Post\Enums\PostStatus;
use Domain\Post\States\DraftState;
use Domain\Post\States\PublishedState;

it('stamps published_at on the first publication', function () {
    $this->travelTo('2026-01-10 12:00:00');
    $post = Post::factory()->create();

    app(ChangePostStatusAction::class)($post, new PublishedState($post->status));

    expect($post->refresh())
        ->status->toBe(PostStatus::Published)
        ->published_at->toDateTimeString()->toBe('2026-01-10 12:00:00');
})->group('Unit', 'Post');

it('keeps the original published_at when unpublishing and republishing', function () {
    $post = Post::factory()->create(['status' => PostStatus::Published, 'published_at' => '2026-01-10 12:00:00']);
    $this->travelTo('2026-02-01 08:00:00');

    app(ChangePostStatusAction::class)($post, new DraftState($post->status));
    expect($post->refresh()->status)->toBe(PostStatus::Draft);

    app(ChangePostStatusAction::class)($post, new PublishedState($post->status));

    expect($post->refresh())
        ->status->toBe(PostStatus::Published)
        ->published_at->toDateTimeString()->toBe('2026-01-10 12:00:00');
})->group('Unit', 'Post');
