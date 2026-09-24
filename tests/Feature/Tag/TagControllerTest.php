<?php

use App\Models\Post\Post;
use App\Models\Tag\Tag;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    test('guests are redirected to the login page', function () {
        $response = $this->get(route('tags.index'));

        $response->assertRedirect(route('login'));
    });

    test('lists active tags ordered by name', function () {
        Tag::factory()->create(['name' => 'Vue']);
        Tag::factory()->create(['name' => 'Laravel']);
        Tag::factory()->trashed()->create(['name' => 'Archived']);

        $response = $this->actingAs(User::factory()->create())->get(route('tags.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('tags/Index')
            ->has('tags.data', 2)
            ->where('tags.data.0.name', 'Laravel')
            ->where('tags.data.1.name', 'Vue')
            ->where('filters', ['search' => null, 'trashed' => false])
        );
    });

    test('shows how many posts use each tag and counts active and trashed tags', function () {
        $tag = Tag::factory()->create(['name' => 'Laravel']);
        Post::factory()->count(2)->hasAttached($tag)->create();
        Post::factory()->trashed()->hasAttached($tag)->create();
        Tag::factory()->create(['name' => 'Vue']);
        Tag::factory()->trashed()->create();

        $response = $this->actingAs(User::factory()->create())->get(route('tags.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('tags.data.0.posts_count', 2)
            ->where('tags.data.1.posts_count', 0)
            ->where('counts', ['active' => 2, 'trashed' => 1])
        );
    });

    test('filters tags by name', function () {
        Tag::factory()->create(['name' => 'Laravel Cloud']);
        Tag::factory()->create(['name' => 'Vue']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('tags.index', ['search' => 'laravel cloud']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('tags.data', 1)
            ->where('tags.data.0.name', 'Laravel Cloud')
            ->where('filters.search', 'laravel cloud')
        );
    });

    test('lists only trashed tags when requested', function () {
        Tag::factory()->create(['name' => 'Vue']);
        Tag::factory()->trashed()->create(['name' => 'Archived']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('tags.index', ['trashed' => 1]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('tags.data', 1)
            ->where('tags.data.0.name', 'Archived')
            ->where('filters.trashed', true)
        );
    });
});

describe('store', function () {
    test('creates a tag with a slug generated from the name', function () {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('tags.store'), ['name' => 'Laravel Cloud']);

        $response->assertRedirect(route('tags.index'));
        $this->assertDatabaseHas('tags', ['name' => 'Laravel Cloud', 'slug' => 'laravel-cloud']);
    });

    test('creates a tag with the given slug', function () {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('tags.store'), ['name' => 'Laravel Cloud', 'slug' => 'cloud']);

        $response->assertRedirect(route('tags.index'));
        $this->assertDatabaseHas('tags', ['name' => 'Laravel Cloud', 'slug' => 'cloud']);
    });

    test('requires a name', function () {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('tags.store'), []);

        $response->assertInvalid(['name' => 'The name field is required.']);
        $this->assertDatabaseCount('tags', 0);
    });

    test('rejects a slug with invalid characters', function () {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('tags.store'), ['name' => 'Vue', 'slug' => 'Vue JS']);

        $response->assertInvalid(['slug' => 'The slug may only contain lowercase letters, numbers and single hyphens.']);
        $this->assertDatabaseCount('tags', 0);
    });

    test('rejects a slug already used by a trashed tag', function () {
        Tag::factory()->trashed()->create(['slug' => 'vue']);

        $response = $this->actingAs(User::factory()->create())
            ->post(route('tags.store'), ['name' => 'Vue', 'slug' => 'vue']);

        $response->assertInvalid(['slug' => 'The slug has already been taken.']);
        $this->assertDatabaseCount('tags', 1);
    });
});

describe('update', function () {
    test('updates the tag name and slug', function () {
        $tag = Tag::factory()->create(['name' => 'Vue', 'slug' => 'vue']);

        $response = $this->actingAs(User::factory()->create())
            ->put(route('tags.update', $tag), ['name' => 'Vue.js', 'slug' => 'vuejs']);

        $response->assertRedirect(route('tags.index'));
        expect($tag->refresh())
            ->name->toBe('Vue.js')
            ->slug->toBe('vuejs');
    });

    test('keeps its own slug without failing the unique rule', function () {
        $tag = Tag::factory()->create(['name' => 'Vue', 'slug' => 'vue']);

        $response = $this->actingAs(User::factory()->create())
            ->put(route('tags.update', $tag), ['name' => 'Vue 3', 'slug' => 'vue']);

        $response->assertValid();
        expect($tag->refresh()->name)->toBe('Vue 3');
    });

    test('rejects a slug used by another tag', function () {
        Tag::factory()->create(['slug' => 'laravel']);
        $tag = Tag::factory()->create(['name' => 'Vue', 'slug' => 'vue']);

        $response = $this->actingAs(User::factory()->create())
            ->put(route('tags.update', $tag), ['name' => 'Vue', 'slug' => 'laravel']);

        $response->assertInvalid(['slug' => 'The slug has already been taken.']);
        expect($tag->refresh()->slug)->toBe('vue');
    });
});

describe('destroy', function () {
    test('moves the tag to the trash', function () {
        $tag = Tag::factory()->create();

        $response = $this->actingAs(User::factory()->create())->delete(route('tags.destroy', $tag));

        $response->assertRedirect(route('tags.index'));
        $this->assertSoftDeleted($tag);
    });
});

describe('restore', function () {
    test('restores a trashed tag', function () {
        $tag = Tag::factory()->trashed()->create();

        $response = $this->actingAs(User::factory()->create())->patch(route('tags.restore', $tag));

        $response->assertRedirect(route('tags.index'));
        $this->assertNotSoftDeleted($tag);
    });
});
