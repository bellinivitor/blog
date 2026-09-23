<?php

use App\Models\Post\Post;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('renders a missing post as the blog 404 page with the two most read posts', function () {
    Post::factory()->published()->create(['title' => 'Most read', 'views_count' => 50]);
    Post::factory()->published()->create(['title' => 'Second', 'views_count' => 10]);
    Post::factory()->published()->create(['title' => 'Third', 'views_count' => 5]);
    Post::factory()->create(['title' => 'Draft', 'views_count' => 99]);

    $response = $this->get(route('blog.posts.show', 'does-not-exist'));

    $response->assertNotFound()->assertInertia(fn (Assert $page) => $page
        ->component('blog/Error')
        ->where('status', 404)
        ->has('suggestions', 2)
        ->where('suggestions.0.title', 'Most read')
        ->where('suggestions.1.title', 'Second')
        ->where('blog.author', config('blog.author'))
    );
});

test('suggests the newest posts when none has been read yet', function () {
    Post::factory()->published()->create(['title' => 'Older', 'published_at' => now()->subDays(2)]);
    Post::factory()->published()->create(['title' => 'Newer', 'published_at' => now()->subDay()]);

    $response = $this->get(route('blog.posts.show', 'does-not-exist'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('suggestions.0.title', 'Newer')
        ->where('suggestions.1.title', 'Older')
    );
});

test('renders the 404 page for a path that matches no route', function () {
    $response = $this->get('/some/unknown/path');

    $response->assertNotFound()->assertInertia(fn (Assert $page) => $page
        ->component('blog/Error')
        ->where('status', 404)
    );
});

test('renders a forbidden action as the 403 page without suggestions', function () {
    $post = Post::factory()->create();

    $response = $this->actingAs(User::factory()->create())->get(route('posts.edit', $post));

    $response->assertForbidden()->assertInertia(fn (Assert $page) => $page
        ->component('blog/Error')
        ->where('status', 403)
        ->has('suggestions', 0)
    );
});

test('renders server errors and maintenance as the blog page outside debug mode', function (int $status) {
    config(['app.debug' => false]);
    Route::get('/_error', fn () => abort($status));

    $response = $this->get('/_error');

    $response->assertStatus($status)->assertInertia(fn (Assert $page) => $page
        ->component('blog/Error')
        ->where('status', $status)
    );
})->with([500, 503]);

test('keeps the detailed Laravel page for a server error in debug mode', function () {
    config(['app.debug' => true]);
    Route::get('/_error', fn () => throw new RuntimeException('Boom'));

    $response = $this->get('/_error');

    $response->assertInternalServerError()
        ->assertSee('Boom');
});

test('sends the author back with a toast when the page expired', function () {
    Route::middleware('web')->post('/_expired', fn () => abort(419));

    $response = $this->from('/admin/posts/create')->post('/_expired');

    $response->assertRedirect('/admin/posts/create');
    expect(session('inertia.flash_data.toast.type'))->toBe('error');
});

test('answers JSON requests with the default JSON error', function () {
    $response = $this->getJson(route('blog.posts.show', 'does-not-exist'));

    $response->assertNotFound()->assertJsonStructure(['message']);
});
