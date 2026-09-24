<?php

use App\Models\Post\Post;
use Inertia\Testing\AssertableInertia as Assert;

test('a reader likes a published post without touching updated_at', function () {
    $post = Post::factory()->published()->create(['slug' => 'hello', 'updated_at' => '2026-01-01 10:00:00']);

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])->postJson(route('blog.posts.like', 'hello'))->assertOk()->assertExactJson(['likes' => 1]);
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.2'])->postJson(route('blog.posts.like', 'hello'))->assertOk()->assertExactJson(['likes' => 2]);

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

test('a visitor likes each post only once a day', function () {
    $post = Post::factory()->published()->create(['slug' => 'hello']);

    $this->postJson(route('blog.posts.like', 'hello'))->assertOk();
    $this->postJson(route('blog.posts.like', 'hello'))->assertTooManyRequests();

    expect($post->fresh()->likes_count)->toBe(1);

    $this->travel(1)->day();
    $this->postJson(route('blog.posts.like', 'hello'))->assertOk();
});

test('a visitor has a small hourly budget of likes across posts', function () {
    foreach (range(1, 21) as $number) {
        Post::factory()->published()->create(['slug' => "post-{$number}"]);
    }

    foreach (range(1, 20) as $number) {
        $this->postJson(route('blog.posts.like', "post-{$number}"))->assertOk();
    }

    $this->postJson(route('blog.posts.like', 'post-21'))->assertTooManyRequests();
});

test('the post page shows its likes', function () {
    Post::factory()->published()->create(['slug' => 'hello', 'likes_count' => 7]);

    $this->get(route('blog.posts.show', 'hello'))
        ->assertInertia(fn (Assert $page) => $page->where('likes', 7));
});
