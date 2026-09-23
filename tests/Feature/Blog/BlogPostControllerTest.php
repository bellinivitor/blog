<?php

use App\Models\Post\Post;
use App\Models\Tag\Tag;
use Domain\Post\Enums\PostStatus;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    test('lists published posts newest first for guests', function () {
        Post::factory()->create(['title' => 'Older', 'status' => PostStatus::Published, 'published_at' => '2026-01-01 10:00:00']);
        Post::factory()->create(['title' => 'Newer', 'status' => PostStatus::Published, 'published_at' => '2026-03-01 10:00:00']);

        $response = $this->get(route('blog.index'));

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

        $response = $this->get(route('blog.index'));

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

        $response = $this->get(route('blog.index'));

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
            ->where('content', fn (string $html) => str_contains($html, '<h2>Intro</h2>')
                && str_contains($html, 'class="phiki language-php'))
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
