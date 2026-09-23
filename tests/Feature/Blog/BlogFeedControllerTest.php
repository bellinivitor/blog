<?php

use App\Models\Post\Post;
use Domain\Post\Enums\PostStatus;

test('serves an rss feed with published posts newest first', function () {
    Post::factory()->create(['title' => 'Older', 'slug' => 'older', 'status' => PostStatus::Published, 'published_at' => '2026-01-01 10:00:00']);
    Post::factory()->create(['title' => 'Newer', 'slug' => 'newer', 'status' => PostStatus::Published, 'published_at' => '2026-03-01 10:00:00']);
    Post::factory()->create(['title' => 'Draft']);

    $response = $this->get(route('blog.feed'));

    $response->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
    $channel = simplexml_load_string($response->getContent())->channel;
    $titles = [];
    foreach ($channel->item as $item) {
        $titles[] = (string) $item->title;
    }
    expect($titles)->toBe(['Newer', 'Older']);
    expect((string) $channel->item[0]->link)->toBe(route('blog.posts.show', 'newer'));
    expect((string) $channel->item[0]->pubDate)->toBe('Sun, 01 Mar 2026 10:00:00 +0000');
});

test('includes the full rendered content of each post', function () {
    Post::factory()->published()->create(['content' => "## Intro\n\nTexto **forte**."]);

    $response = $this->get(route('blog.feed'));

    $content = simplexml_load_string($response->getContent())
        ->channel->item[0]->children('http://purl.org/rss/1.0/modules/content/')->encoded;
    expect((string) $content)
        ->toContain('<h2 id="intro">Intro</h2>')
        ->toContain('<strong>forte</strong>');
});

test('keeps at most the twenty latest posts', function () {
    Post::factory()->published()->count(21)->create();

    $response = $this->get(route('blog.feed'));

    expect(simplexml_load_string($response->getContent())->channel->item)->toHaveCount(20);
});

test('blog pages advertise the feed in the server-rendered html', function () {
    $response = $this->get(route('home'));

    $response->assertSee('<link rel="alternate" type="application/rss+xml" title="'.config('blog.author').'" href="'.route('blog.feed').'">', false);
});

test('admin pages do not advertise the feed', function () {
    $response = $this->get(route('login'));

    $response->assertDontSee('application/rss+xml', false);
});

test('dates revised posts and the channel by their latest revision', function () {
    Post::factory()->published()->create(['published_at' => '2026-01-10 10:00:00', 'revised_at' => '2026-02-20 15:30:00']);
    Post::factory()->published()->create(['published_at' => '2026-02-01 10:00:00']);

    $channel = simplexml_load_string($this->get(route('blog.feed'))->getContent())->channel;
    $updated = fn (int $index): string => (string) $channel->item[$index]->children('http://www.w3.org/2005/Atom')->updated;

    expect((string) $channel->lastBuildDate)->toBe('Fri, 20 Feb 2026 15:30:00 +0000')
        ->and($updated(1))->toBe('2026-02-20T15:30:00+00:00')
        ->and($updated(0))->toBe('');
});
