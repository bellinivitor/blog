<?php

use App\Models\Post\Post;
use Inertia\Testing\AssertableInertia as Assert;

test('a reader likes a published post without touching updated_at', function () {
    $post = Post::factory()->published()->create(['slug' => 'hello', 'updated_at' => '2026-01-01 10:00:00']);

    $this->postJson(route('blog.posts.like', 'hello'))->assertOk()->assertExactJson(['likes' => 1]);
    $this->postJson(route('blog.posts.like', 'hello'))->assertOk()->assertExactJson(['likes' => 2]);

    expect($post->fresh())
        ->likes_count->toBe(2)
        ->updated_at->toDateTimeString()->toBe('2026-01-01 10:00:00');
});

test('drafts and trashed posts cannot be liked', function (Post $post) {
    $this->postJson(route('blog.posts.like', $post->slug))->assertNotFound();

    expect(Post::withTrashed()->find($post->id)->likes_count)->toBe(0);
})->with([
    'draft' => fn () => Post::factory()->create(['slug' => 'draft']),
    'trashed' => fn () => Post::factory()->published()->trashed()->create(['slug' => 'trashed']),
]);

test('limits how often the same client can like', function () {
    Post::factory()->published()->create(['slug' => 'hello']);

    foreach (range(1, 10) as $attempt) {
        $this->postJson(route('blog.posts.like', 'hello'))->assertOk();
    }

    $this->postJson(route('blog.posts.like', 'hello'))->assertTooManyRequests();
});

test('the post page shows its likes', function () {
    Post::factory()->published()->create(['slug' => 'hello', 'likes_count' => 7]);

    $this->get(route('blog.posts.show', 'hello'))
        ->assertInertia(fn (Assert $page) => $page->where('likes', 7));
});
