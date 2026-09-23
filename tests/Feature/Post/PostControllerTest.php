<?php

use App\Models\Post\Post;
use App\Models\Tag\Tag;
use App\Models\User;
use Domain\Post\Enums\PostStatus;
use Inertia\Testing\AssertableInertia as Assert;

function validPostPayload(array $overrides = []): array
{
    return [
        'title' => 'Hello World',
        'excerpt' => 'A short summary.',
        'content' => '# Hello',
        ...$overrides,
    ];
}

describe('index', function () {
    test('guests are redirected to the login page', function () {
        $response = $this->get(route('posts.index'));

        $response->assertRedirect(route('login'));
    });

    test('lists only the posts of the authenticated author', function () {
        $author = User::factory()->create();
        $tag = Tag::factory()->create(['name' => 'Laravel']);
        Post::factory()->for($author, 'author')->hasAttached($tag)->create(['title' => 'Mine']);
        Post::factory()->create(['title' => 'Someone else']);

        $response = $this->actingAs($author)->get(route('posts.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('posts/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Mine')
            ->where('posts.data.0.tags.0.name', 'Laravel')
            ->has('tags', 1)
            ->where('filters', ['search' => null, 'status' => null, 'tag_id' => null, 'trashed' => false])
        );
    });

    test('filters posts by title', function () {
        $author = User::factory()->create();
        Post::factory()->for($author, 'author')->create(['title' => 'Deploying Laravel apps']);
        Post::factory()->for($author, 'author')->create(['title' => 'Vue tips']);

        $response = $this->actingAs($author)->get(route('posts.index', ['search' => 'deploying laravel']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Deploying Laravel apps')
        );
    });

    test('filters posts by status', function () {
        $author = User::factory()->create();
        Post::factory()->for($author, 'author')->published()->create(['title' => 'Live']);
        Post::factory()->for($author, 'author')->create(['title' => 'Draft']);

        $response = $this->actingAs($author)->get(route('posts.index', ['status' => 'published']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Live')
            ->where('filters.status', 'published')
        );
    });

    test('filters posts by tag', function () {
        $author = User::factory()->create();
        $tag = Tag::factory()->create();
        Post::factory()->for($author, 'author')->hasAttached($tag)->create(['title' => 'Tagged']);
        Post::factory()->for($author, 'author')->create(['title' => 'Untagged']);

        $response = $this->actingAs($author)->get(route('posts.index', ['tag_id' => $tag->id]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Tagged')
        );
    });

    test('lists only trashed posts when requested', function () {
        $author = User::factory()->create();
        Post::factory()->for($author, 'author')->create(['title' => 'Active']);
        Post::factory()->for($author, 'author')->trashed()->create(['title' => 'Trashed']);

        $response = $this->actingAs($author)->get(route('posts.index', ['trashed' => 1]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Trashed')
        );
    });

    test('rejects an unknown status filter', function () {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('posts.index', ['status' => 'archived']));

        $response->assertInvalid(['status' => 'The selected status is invalid.']);
    });
});

describe('create', function () {
    test('renders the create page with the active tags', function () {
        Tag::factory()->create(['name' => 'Laravel']);
        Tag::factory()->trashed()->create();

        $response = $this->actingAs(User::factory()->create())->get(route('posts.create'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('posts/Create')
            ->has('tags', 1)
            ->where('tags.0.name', 'Laravel')
        );
    });
});

describe('store', function () {
    test('creates a draft post owned by the author with its tags', function () {
        $author = User::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this->actingAs($author)
            ->post(route('posts.store'), validPostPayload(['tag_ids' => $tags->modelKeys()]));

        $post = Post::query()->sole();
        $response->assertRedirect(route('posts.edit', $post));
        expect($post)
            ->title->toBe('Hello World')
            ->slug->toBe('hello-world')
            ->excerpt->toBe('A short summary.')
            ->content->toBe('# Hello')
            ->status->toBe(PostStatus::Draft)
            ->published_at->toBeNull()
            ->author_id->toBe($author->id);
        expect($post->tags->modelKeys())->toEqualCanonicalizing($tags->modelKeys());
    });

    test('requires a title and content', function () {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('posts.store'), []);

        $response->assertInvalid([
            'title' => 'The title field is required.',
            'content' => 'The content field is required.',
        ]);
        $this->assertDatabaseCount('posts', 0);
    });

    test('rejects a slug already used by a trashed post', function () {
        Post::factory()->trashed()->create(['slug' => 'hello-world']);

        $response = $this->actingAs(User::factory()->create())
            ->post(route('posts.store'), validPostPayload(['slug' => 'hello-world']));

        $response->assertInvalid(['slug' => 'The slug has already been taken.']);
        $this->assertDatabaseCount('posts', 1);
    });

    test('rejects a trashed tag', function () {
        $tag = Tag::factory()->trashed()->create();

        $response = $this->actingAs(User::factory()->create())
            ->post(route('posts.store'), validPostPayload(['tag_ids' => [$tag->id]]));

        $response->assertInvalid(['tag_ids.0' => 'The selected tag does not exist.']);
        $this->assertDatabaseCount('posts', 0);
    });
});

describe('edit', function () {
    test('renders the edit page with the post and its tags', function () {
        $author = User::factory()->create();
        $tag = Tag::factory()->create(['name' => 'Laravel']);
        $post = Post::factory()->for($author, 'author')->hasAttached($tag)->create(['title' => 'Hello']);

        $response = $this->actingAs($author)->get(route('posts.edit', $post));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('posts/Edit')
            ->where('post.id', $post->id)
            ->where('post.title', 'Hello')
            ->where('post.status', 'draft')
            ->where('post.tags.0.id', $tag->id)
            ->has('tags', 1)
        );
    });
});

describe('update', function () {
    test('updates the post and replaces its tags', function () {
        $author = User::factory()->create();
        [$oldTag, $newTag] = Tag::factory()->count(2)->create();
        $post = Post::factory()->for($author, 'author')->hasAttached($oldTag)->create(['slug' => 'old']);

        $response = $this->actingAs($author)->put(route('posts.update', $post), validPostPayload([
            'title' => 'Updated title',
            'slug' => 'old',
            'tag_ids' => [$newTag->id],
        ]));

        $response->assertRedirect(route('posts.edit', $post));
        $post->refresh();
        expect($post)
            ->title->toBe('Updated title')
            ->slug->toBe('old');
        expect($post->tags->modelKeys())->toBe([$newTag->id]);
    });

    test('removes every tag when none is sent', function () {
        $author = User::factory()->create();
        $post = Post::factory()->for($author, 'author')->hasAttached(Tag::factory())->create();

        $this->actingAs($author)->put(route('posts.update', $post), validPostPayload());

        expect($post->tags()->count())->toBe(0);
    });
});

describe('destroy', function () {
    test('moves the post to the trash', function () {
        $author = User::factory()->create();
        $post = Post::factory()->for($author, 'author')->create();

        $response = $this->actingAs($author)->delete(route('posts.destroy', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertSoftDeleted($post);
    });
});

describe('restore', function () {
    test('restores a trashed post', function () {
        $author = User::factory()->create();
        $post = Post::factory()->for($author, 'author')->trashed()->create();

        $response = $this->actingAs($author)->patch(route('posts.restore', $post));

        $response->assertRedirect(route('posts.index'));
        $this->assertNotSoftDeleted($post);
    });
});

test('forbids managing a post of another author', function (string $method, string $route, bool $trashed) {
    $post = Post::factory()->state(['deleted_at' => $trashed ? now() : null])->create(['title' => 'Original']);

    $response = $this->actingAs(User::factory()->create())
        ->{$method}(route($route, $post), validPostPayload(['title' => 'Hijacked']));

    $response->assertForbidden();
    expect($post->fresh())
        ->title->toBe('Original')
        ->trashed()->toBe($trashed);
})->with([
    'edit' => ['get', 'posts.edit', false],
    'update' => ['put', 'posts.update', false],
    'destroy' => ['delete', 'posts.destroy', false],
    'restore' => ['patch', 'posts.restore', true],
]);

describe('publish', function () {
    test('publishes a draft post', function () {
        $author = User::factory()->create();
        $post = Post::factory()->for($author, 'author')->create();

        $response = $this->actingAs($author)
            ->from(route('posts.edit', $post))
            ->patch(route('posts.publish', $post));

        $response->assertRedirect(route('posts.edit', $post));
        expect($post->refresh())
            ->status->toBe(PostStatus::Published)
            ->published_at->not->toBeNull();
    });

    test('returns 409 when the post is already published', function () {
        $author = User::factory()->create();
        $post = Post::factory()->for($author, 'author')->published()->create();

        $response = $this->actingAs($author)->patch(route('posts.publish', $post));

        $response->assertConflict();
    });
});

describe('unpublish', function () {
    test('moves a published post back to draft', function () {
        $author = User::factory()->create();
        $post = Post::factory()->for($author, 'author')->published()->create();

        $response = $this->actingAs($author)
            ->from(route('posts.index'))
            ->patch(route('posts.unpublish', $post));

        $response->assertRedirect(route('posts.index'));
        expect($post->refresh()->status)->toBe(PostStatus::Draft);
    });
});

test('forbids changing the status of a post of another author', function (string $route, PostStatus $status) {
    $post = Post::factory()->create(['status' => $status]);

    $response = $this->actingAs(User::factory()->create())->patch(route($route, $post));

    $response->assertForbidden();
    expect($post->refresh()->status)->toBe($status);
})->with([
    'publish' => ['posts.publish', PostStatus::Draft],
    'unpublish' => ['posts.unpublish', PostStatus::Published],
]);
