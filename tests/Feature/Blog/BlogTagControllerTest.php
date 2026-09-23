<?php

use App\Models\Post\Post;
use App\Models\Tag\Tag;
use Inertia\Testing\AssertableInertia as Assert;

test('lists the published posts of a tag', function () {
    $tag = Tag::factory()->create(['name' => 'Vue', 'slug' => 'vue']);
    Post::factory()->published()->hasAttached($tag)->create(['title' => 'Tagged']);
    Post::factory()->hasAttached($tag)->create(['title' => 'Draft']);
    Post::factory()->published()->create(['title' => 'Other tag']);

    $response = $this->get(route('blog.tags.show', 'vue'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('blog/Tag')
        ->where('tag.name', 'Vue')
        ->has('posts', 1)
        ->where('posts.0.title', 'Tagged')
    );
});

test('returns 404 for a trashed tag', function () {
    Tag::factory()->trashed()->create(['slug' => 'vue']);

    $response = $this->get(route('blog.tags.show', 'vue'));

    $response->assertNotFound();
});
