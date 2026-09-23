<?php

use App\Models\Post\Post;
use App\Models\User;
use Domain\Post\Policies\PostPolicy;

test('the author may manage the post', function (string $ability) {
    $author = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create();

    expect((new PostPolicy)->{$ability}($author, $post))->toBeTrue();
})->with(['update', 'delete', 'restore'])->group('Unit', 'Post');

test('other users may not manage the post', function (string $ability) {
    $post = Post::factory()->create();

    expect((new PostPolicy)->{$ability}(User::factory()->create(), $post))->toBeFalse();
})->with(['update', 'delete', 'restore'])->group('Unit', 'Post');
