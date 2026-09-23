<?php

use Domain\Post\Enums\PostStatus;
use Domain\Post\Exceptions\InvalidPostStatusTransitionException;
use Domain\Post\States\DraftState;
use Domain\Post\States\PublishedState;

it('publishes a draft', function () {
    expect((new PublishedState(PostStatus::Draft))->handle())->toBe(PostStatus::Published);
})->group('Unit', 'Post');

it('moves a published post back to draft', function () {
    expect((new DraftState(PostStatus::Published))->handle())->toBe(PostStatus::Draft);
})->group('Unit', 'Post');

it('rejects a transition to the current status', function (string $stateClass, PostStatus $status) {
    expect(fn () => (new $stateClass($status))->handle())
        ->toThrow(InvalidPostStatusTransitionException::class, "The post is already {$status->value}.");
})->with([
    'publishing a published post' => [PublishedState::class, PostStatus::Published],
    'unpublishing a draft' => [DraftState::class, PostStatus::Draft],
])->group('Unit', 'Post');
