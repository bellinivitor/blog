<?php

use App\Models\Post\Post;
use App\Models\Post\PostDailyView;
use App\Models\User;
use Domain\Post\Actions\DiscardPostViewSaltAction;
use Domain\Post\Actions\RecordPostViewAction;
use Illuminate\Support\Facades\Cache;

const BROWSER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';

test('counts a view from a reader without touching updated_at', function () {
    $post = Post::factory()->published()->create(['slug' => 'hello', 'updated_at' => '2026-01-01 10:00:00']);

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])->withHeader('User-Agent', BROWSER_AGENT)->get(route('blog.posts.show', 'hello'));
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.2'])->withHeader('User-Agent', BROWSER_AGENT)->get(route('blog.posts.show', 'hello'));

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

test('adds the views to the day they happened in the blog timezone', function () {
    config(['blog.timezone' => 'America/Sao_Paulo']);
    $post = Post::factory()->published()->create(['slug' => 'hello', 'published_at' => '2026-09-01 10:00:00']);

    // 01:00 UTC is still the previous evening in São Paulo.
    $this->travelTo('2026-09-23 01:00:00');
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])->withHeader('User-Agent', BROWSER_AGENT)->get(route('blog.posts.show', 'hello'));
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.2'])->withHeader('User-Agent', BROWSER_AGENT)->get(route('blog.posts.show', 'hello'));

    $this->travelTo('2026-09-23 12:00:00');
    $this->withHeader('User-Agent', BROWSER_AGENT)->get(route('blog.posts.show', 'hello'));

    expect(PostDailyView::query()->where('post_id', $post->id)->orderBy('date')->get()
        ->map(fn (PostDailyView $row): array => [$row->date->toDateString(), $row->views])
        ->all()
    )->toBe([['2026-09-22', 2], ['2026-09-23', 1]]);
});

test('does not add ignored views to the daily count', function () {
    Post::factory()->published()->create(['slug' => 'hello']);

    $this->actingAs(User::factory()->create())
        ->withHeader('User-Agent', BROWSER_AGENT)
        ->get(route('blog.posts.show', 'hello'));

    expect(PostDailyView::query()->count())->toBe(0);
});

test('counts a reader once per post per day, however often they reload', function () {
    config(['blog.timezone' => 'America/Sao_Paulo']);
    $post = Post::factory()->published()->create(['slug' => 'hello', 'published_at' => '2026-09-01 10:00:00']);
    $other = Post::factory()->published()->create(['slug' => 'other', 'published_at' => '2026-09-01 10:00:00']);
    $read = fn (string $slug) => $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])
        ->withHeader('User-Agent', BROWSER_AGENT)
        ->get(route('blog.posts.show', $slug));

    $this->travelTo('2026-09-23 12:00:00');
    $read('hello');
    $read('hello');
    $read('hello');
    $read('other');

    $this->travelTo('2026-09-24 12:00:00');
    $read('hello');

    expect($post->fresh()->views_count)->toBe(2)
        ->and($other->fresh()->views_count)->toBe(1);
});

test('tells apart readers behind the same IP by their browser', function () {
    $post = Post::factory()->published()->create(['slug' => 'hello']);

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])->withHeader('User-Agent', BROWSER_AGENT)->get(route('blog.posts.show', 'hello'));
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.1'])->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/128.0 Mobile Safari/537.36')->get(route('blog.posts.show', 'hello'));

    expect($post->fresh()->views_count)->toBe(2);
});

test('discards the visitor salt of the previous day and keeps today\'s', function () {
    config(['blog.timezone' => 'America/Sao_Paulo']);
    $this->travelTo('2026-09-24 03:05:00');
    // Long TTLs: only the action, not expiry, may remove the salt.
    Cache::put(RecordPostViewAction::saltKey('2026-09-23'), 'yesterday', now()->addYear());
    Cache::put(RecordPostViewAction::saltKey('2026-09-24'), 'today', now()->addYear());

    app(DiscardPostViewSaltAction::class)();

    expect(Cache::has(RecordPostViewAction::saltKey('2026-09-23')))->toBeFalse()
        ->and(Cache::get(RecordPostViewAction::saltKey('2026-09-24')))->toBe('today');
});
