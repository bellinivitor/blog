<?php

use App\Models\Post\Post;
use App\Models\User;

const BROWSER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';

test('counts a view from a reader without touching updated_at', function () {
    $post = Post::factory()->published()->create(['slug' => 'hello', 'updated_at' => '2026-01-01 10:00:00']);

    $this->withHeader('User-Agent', BROWSER_AGENT)->get(route('blog.posts.show', 'hello'));
    $this->withHeader('User-Agent', BROWSER_AGENT)->get(route('blog.posts.show', 'hello'));

    expect($post->fresh())
        ->views_count->toBe(2)
        ->updated_at->toDateTimeString()->toBe('2026-01-01 10:00:00');
});

test('does not count the signed-in author', function () {
    $post = Post::factory()->published()->create(['slug' => 'hello']);

    $this->actingAs(User::factory()->create())
        ->withHeader('User-Agent', BROWSER_AGENT)
        ->get(route('blog.posts.show', 'hello'));

    expect($post->fresh()->views_count)->toBe(0);
});

test('does not count crawlers, link previews or missing user agents', function (string $agent) {
    $post = Post::factory()->published()->create(['slug' => 'hello']);

    $this->withHeader('User-Agent', $agent)->get(route('blog.posts.show', 'hello'));

    expect($post->fresh()->views_count)->toBe(0);
})->with([
    'googlebot' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
    'linkedin preview' => 'LinkedInBot/1.0 (compatible; Mozilla/5.0; Apache-HttpClient +http://www.linkedin.com)',
    'whatsapp preview' => 'WhatsApp/2.23.20.0',
    'curl' => 'curl/8.4.0',
    'empty' => '',
]);
