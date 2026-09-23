<?php

use App\Models\Post\Post;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('shows total views and the most read published posts of the author', function () {
    $author = User::factory()->create();
    Post::factory()->for($author, 'author')->published()->create(['title' => 'Popular', 'views_count' => 30]);
    Post::factory()->for($author, 'author')->published()->create(['title' => 'Quiet', 'views_count' => 5]);
    Post::factory()->for($author, 'author')->published()->create(['title' => 'Unread', 'views_count' => 0]);
    Post::factory()->for($author, 'author')->create(['title' => 'Draft', 'views_count' => 99]);
    Post::factory()->published()->create(['title' => 'Someone else', 'views_count' => 500]);

    $response = $this->actingAs($author)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('totalViews', 35)
        ->has('mostRead', 2)
        ->where('mostRead.0.title', 'Popular')
        ->where('mostRead.0.views_count', 30)
        ->where('mostRead.1.title', 'Quiet')
    );
});
