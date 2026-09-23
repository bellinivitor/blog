<?php

use App\Models\Post\Post;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('a new post is born with its preview link disabled', function () {
    $author = User::factory()->create();

    $this->actingAs($author)->post(route('posts.store'), [
        'title' => 'Hello World',
        'content' => '# Hello',
    ]);

    $post = Post::query()->sole();
    expect($post->preview_token)->toBeNull();
    $this->actingAs($author)->get(route('posts.edit', $post))
        ->assertInertia(fn (Assert $page) => $page->where('post.preview_url', null));
});

test('enabling the link shares a public preview url in the editor', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create();

    $response = $this->actingAs($author)
        ->from(route('posts.edit', $post))
        ->post(route('posts.preview-link.store', $post));

    $response->assertRedirect(route('posts.edit', $post));
    $token = $post->refresh()->preview_token;
    expect($token)->toHaveLength(40);
    $this->actingAs($author)->get(route('posts.edit', $post))
        ->assertInertia(fn (Assert $page) => $page->where('post.preview_url', route('preview.show', $token)));
});

test('enabling an already enabled link keeps the same url', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create(['preview_token' => str_repeat('a', 40)]);

    $this->actingAs($author)->post(route('posts.preview-link.store', $post));

    expect($post->refresh()->preview_token)->toBe(str_repeat('a', 40));
});

test('disabling the link kills it, and enabling again creates a new one', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->create(['preview_token' => str_repeat('a', 40)]);

    $response = $this->actingAs($author)
        ->from(route('posts.edit', $post))
        ->delete(route('posts.preview-link.destroy', $post));

    $response->assertRedirect(route('posts.edit', $post));
    expect($post->refresh()->preview_token)->toBeNull();

    $this->actingAs($author)->post(route('posts.preview-link.store', $post));

    expect($post->refresh()->preview_token)
        ->not->toBeNull()
        ->not->toBe(str_repeat('a', 40));
});

test('guests cannot change the preview link', function (string $method, string $route) {
    $post = Post::factory()->create();

    $response = $this->{$method}(route($route, $post));

    $response->assertRedirect(route('login'));
    expect($post->refresh()->preview_token)->toBeNull();
})->with([
    'enable' => ['post', 'posts.preview-link.store'],
    'disable' => ['delete', 'posts.preview-link.destroy'],
]);

test('forbids changing the preview link of a post of another author', function (string $method, string $route) {
    $post = Post::factory()->create(['preview_token' => str_repeat('a', 40)]);

    $response = $this->actingAs(User::factory()->create())->{$method}(route($route, $post));

    $response->assertForbidden();
    expect($post->refresh()->preview_token)->toBe(str_repeat('a', 40));
})->with([
    'enable' => ['post', 'posts.preview-link.store'],
    'disable' => ['delete', 'posts.preview-link.destroy'],
]);
