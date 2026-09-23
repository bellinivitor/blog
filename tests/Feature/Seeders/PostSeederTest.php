<?php

use App\Models\Post\Post;
use App\Models\Tag\Tag;
use App\Models\User;
use Database\Seeders\PostSeeder;
use Domain\Post\Enums\PostStatus;

test('seeds eight published posts and two drafts with their tags', function () {
    $author = User::factory()->create();

    $this->seed(PostSeeder::class);

    expect(Post::query()->count())->toBe(10);
    expect(Post::query()->published()->count())->toBe(8);
    expect(Post::query()->where('status', PostStatus::Draft)->whereNull('published_at')->count())->toBe(2);
    expect(Post::query()->where('author_id', '!=', $author->id)->exists())->toBeFalse();

    $post = Post::query()->where('slug', 'actions-no-lugar-de-services-no-laravel')->sole();
    expect($post->content)->toContain('## O que muda com uma Action');
    expect($post->tags->pluck('slug')->sort()->values()->all())->toBe(['arquitetura', 'laravel']);
});

test('can run again without duplicating posts or tags', function () {
    User::factory()->create();
    $this->seed(PostSeeder::class);
    $tagCount = Tag::query()->count();

    $this->seed(PostSeeder::class);

    expect(Post::query()->count())->toBe(10);
    expect(Tag::query()->count())->toBe($tagCount);
});
