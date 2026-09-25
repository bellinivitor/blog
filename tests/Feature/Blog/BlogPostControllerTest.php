<?php

use App\Models\Post\Post;
use App\Models\Tag\Tag;
use Domain\Post\Enums\PostStatus;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    test('lists published posts newest first for guests', function () {
        Post::factory()->create(['title' => 'Older', 'status' => PostStatus::Published, 'published_at' => '2026-01-01 10:00:00']);
        Post::factory()->create(['title' => 'Newer', 'status' => PostStatus::Published, 'published_at' => '2026-03-01 10:00:00']);

        $response = $this->get(route('home'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('blog/Index')
            ->has('posts', 2)
            ->where('posts.0.title', 'Newer')
            ->where('posts.1.title', 'Older')
            ->where('blog.author', config('blog.author'))
        );
    });

    test('hides drafts, trashed posts and posts scheduled for later', function () {
        $this->travelTo('2026-05-01 12:00:00');
        Post::factory()->published()->create(['title' => 'Visible']);
        Post::factory()->create(['title' => 'Draft']);
        Post::factory()->published()->trashed()->create(['title' => 'Trashed']);
        Post::factory()->create(['title' => 'Future', 'status' => PostStatus::Published, 'published_at' => '2026-06-01 12:00:00']);

        $response = $this->get(route('home'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('posts', 1)
            ->where('posts.0.title', 'Visible')
        );
    });

    test('exposes only public fields of a post', function () {
        $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        Post::factory()->published()->hasAttached($tag)->create([
            'title' => 'Hello',
            'slug' => 'hello',
            'excerpt' => 'Summary',
            'content' => str_repeat('word ', 401),
        ]);

        $response = $this->get(route('home'));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('posts.0', fn (Assert $post) => $post
                ->where('title', 'Hello')
                ->where('slug', 'hello')
                ->where('excerpt', 'Summary')
                ->where('reading_minutes', 3)
                ->where('tags.0.slug', 'laravel')
                ->has('published_at')
                ->missing('content')
                ->missing('status')
                ->etc()
            )
        );
    });
});

test('redirects addresses from before posts moved to the root', function (string $old, string $new) {
    $response = $this->get($old);

    $response->assertMovedPermanently()->assertRedirect($new);
})->with([
    'home' => ['/blog', '/'],
    'post' => ['/blog/hello', '/hello'],
    'tag' => ['/blog/tags/vue', '/tags/vue'],
    'feed' => ['/blog/feed', '/feed'],
    'dashboard' => ['/dashboard', '/admin'],
    'login' => ['/login', '/admin/login'],
]);

test('serves posts at the root of the site', function () {
    Post::factory()->published()->create(['slug' => 'hello']);

    $this->get('/hello')->assertOk();
});

describe('show', function () {
    test('renders the post markdown to html with highlighted code', function () {
        Post::factory()->published()->create([
            'slug' => 'hello',
            'content' => "## Intro\n\n```php\necho 'hi';\n```",
        ]);

        $response = $this->get(route('blog.posts.show', 'hello'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('blog/Show')
            ->where('post.slug', 'hello')
            ->where('content', fn (string $html) => str_contains($html, '<h2 id="intro">Intro</h2>')
                && str_contains($html, 'class="phiki language-php'))
        );
    });

    test('highlights code whose language the editor wrote capitalized', function () {
        Post::factory()->published()->create([
            'slug' => 'hello',
            'content' => "```PHP\necho 'hi';\n```",
        ]);

        $response = $this->get(route('blog.posts.show', 'hello'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('content', fn (string $html) => str_contains($html, 'class="phiki language-php'))
        );
    });

    test('keeps mermaid blocks as escaped diagram source instead of highlighting them', function () {
        Post::factory()->published()->create([
            'slug' => 'hello',
            'content' => "```mermaid\nflowchart LR\n  A --> B<script>\n```",
        ]);

        $response = $this->get(route('blog.posts.show', 'hello'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('content', fn (string $html) => str_contains($html, "<pre class=\"mermaid\">flowchart LR\n  A --&gt; B&lt;script&gt;\n</pre>")
                && ! str_contains($html, 'phiki'))
        );
    });

    test('gives h2 and h3 headings unique ids for the table of contents', function () {
        Post::factory()->published()->create([
            'slug' => 'hello',
            'content' => "# Top\n\n## O mecanismo\n\n### Detalhe\n\n## O mecanismo",
        ]);

        $response = $this->get(route('blog.posts.show', 'hello'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('content', fn (string $html) => str_contains($html, '<h1>Top</h1>')
                && str_contains($html, '<h2 id="o-mecanismo">O mecanismo</h2>')
                && str_contains($html, '<h3 id="detalhe">Detalhe</h3>')
                && str_contains($html, '<h2 id="o-mecanismo-1">O mecanismo</h2>'))
        );
    });

    test('links the previous (older) and next (newer) published posts', function () {
        Post::factory()->create(['slug' => 'first', 'status' => PostStatus::Published, 'published_at' => '2026-01-01 10:00:00']);
        Post::factory()->create(['slug' => 'draft-between', 'published_at' => null]);
        Post::factory()->create(['slug' => 'middle', 'status' => PostStatus::Published, 'published_at' => '2026-02-01 10:00:00']);
        Post::factory()->create(['slug' => 'last', 'status' => PostStatus::Published, 'published_at' => '2026-03-01 10:00:00']);

        $response = $this->get(route('blog.posts.show', 'middle'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('previous.slug', 'first')
            ->where('next.slug', 'last')
        );
    });

    test('has no previous post for the oldest and no next for the newest', function () {
        Post::factory()->create(['slug' => 'only', 'status' => PostStatus::Published, 'published_at' => '2026-01-01 10:00:00']);

        $response = $this->get(route('blog.posts.show', 'only'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('previous', null)
            ->where('next', null)
        );
    });

    test('suggests up to three published posts sharing tags, most shared first', function () {
        [$laravel, $vue, $php] = Tag::factory()->count(3)->create();
        $post = Post::factory()->published()->hasAttached([$laravel, $vue])->create(['slug' => 'current']);
        Post::factory()->published()->hasAttached($laravel)->create(['slug' => 'one-tag', 'published_at' => now()->subDay()]);
        Post::factory()->published()->hasAttached([$laravel, $vue])->create(['slug' => 'two-tags', 'published_at' => now()->subDays(30)]);
        Post::factory()->hasAttached($laravel)->create(['slug' => 'draft']);
        Post::factory()->published()->hasAttached($php)->create(['slug' => 'unrelated']);

        $response = $this->get(route('blog.posts.show', $post->slug));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('related', 3)
            ->where('related.0.slug', 'two-tags')
            ->where('related.1.slug', 'one-tag')
            ->where('related.2.slug', 'unrelated')
        );
    });

    test('ranks a post sharing a rare tag above one sharing a common tag', function () {
        $common = Tag::factory()->create();
        $rare = Tag::factory()->create();
        $post = Post::factory()->published()->hasAttached([$common, $rare])->create(['slug' => 'current']);
        Post::factory()->published()->hasAttached($common)->create(['slug' => 'common-newer', 'published_at' => now()->subDay()]);
        Post::factory()->published()->hasAttached($common)->count(3)->create(['published_at' => now()->subYear()]);
        Post::factory()->published()->hasAttached($rare)->create(['slug' => 'rare-older', 'published_at' => now()->subMonth()]);

        $response = $this->get(route('blog.posts.show', $post->slug));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('related.0.slug', 'rare-older')
            ->where('related.1.slug', 'common-newer')
        );
    });

    test('fills the related list with the latest posts when few share tags', function () {
        $post = Post::factory()->published()->create(['slug' => 'no-tags']);
        Post::factory()->published()->create(['slug' => 'latest', 'published_at' => now()->subHour()]);
        Post::factory()->published()->create(['slug' => 'older', 'published_at' => now()->subWeek()]);
        Post::factory()->create(['slug' => 'draft']);

        $response = $this->get(route('blog.posts.show', $post->slug));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('related', 2)
            ->where('related.0.slug', 'latest')
            ->where('related.1.slug', 'older')
        );
    });

    test('escapes raw html written in the markdown', function () {
        Post::factory()->published()->create([
            'slug' => 'hello',
            'content' => '<script>alert(1)</script>',
        ]);

        $response = $this->get(route('blog.posts.show', 'hello'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('content', fn (string $html) => ! str_contains($html, '<script>')
                && str_contains($html, '&lt;script&gt;'))
        );
    });

    test('returns 404 for posts that are not public', function (Closure $makePost) {
        $makePost();

        $response = $this->get(route('blog.posts.show', 'hidden'));

        $response->assertNotFound();
    })->with([
        'draft' => fn () => fn () => Post::factory()->create(['slug' => 'hidden']),
        'trashed' => fn () => fn () => Post::factory()->published()->trashed()->create(['slug' => 'hidden']),
        'scheduled' => fn () => fn () => Post::factory()->create(['slug' => 'hidden', 'status' => PostStatus::Published, 'published_at' => now()->addDay()]),
    ]);
});
