<?php

use App\Models\Post\Post;
use Inertia\Testing\AssertableInertia as Assert;

test('anyone with the link reads a draft, without it being indexed or counted', function () {
    $token = str_repeat('a', 40);
    $post = Post::factory()->create(['title' => 'Rascunho', 'content' => '## Intro', 'preview_token' => $token]);

    $response = $this->withHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) Safari/605.1.15')
        ->get(route('preview.show', $token));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('blog/Show')
        ->where('post.title', 'Rascunho')
        ->where('post.published_at', null)
        ->where('content', '<h2 id="intro">Intro</h2>'."\n")
        ->where('preview.editUrl', null)
    );
    $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    expect($post->fresh()->views_count)->toBe(0);
});

test('a link that does not match an enabled preview is not found', function () {
    Post::factory()->create(['preview_token' => str_repeat('a', 40)]);

    $this->get('/preview/'.str_repeat('b', 40))->assertNotFound();
});

test('a trashed post is not reachable through its preview link', function () {
    $token = str_repeat('a', 40);
    Post::factory()->create(['preview_token' => $token, 'deleted_at' => now()]);

    $this->get(route('preview.show', $token))->assertNotFound();
});
