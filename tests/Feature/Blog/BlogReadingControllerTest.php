<?php

use App\Models\Reading\Reading;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('lists the readings newest first, without trashed ones', function () {
    Reading::factory()->create(['title' => 'Older', 'created_at' => now()->subDay()]);
    Reading::factory()->create(['title' => 'Newer', 'url' => 'https://example.com/newer']);
    Reading::factory()->trashed()->create(['title' => 'Archived']);

    $response = $this->get(route('blog.readings.index'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('blog/Readings')
        ->has('readings', 2)
        ->where('readings.0.title', 'Newer')
        ->where('readings.0.url', 'https://example.com/newer')
        ->where('readings.1.title', 'Older')
    );
});

test('keeps the readings page slug away from posts', function () {
    $response = $this->actingAs(User::factory()->create())
        ->post(route('posts.store'), ['title' => 'Leituras', 'slug' => 'leituras', 'content' => 'Texto']);

    $response->assertInvalid(['slug']);
});
