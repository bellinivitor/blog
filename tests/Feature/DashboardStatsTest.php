<?php

use App\Models\Post\Post;
use App\Models\Post\PostDailyView;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['blog.timezone' => 'America/Sao_Paulo']);
    $this->travelTo('2026-09-23 15:00:00');
});

test('shows total views and likes and the most read published posts of the author', function () {
    $author = User::factory()->create();
    Post::factory()->for($author, 'author')->published()->create(['title' => 'Popular', 'views_count' => 30, 'likes_count' => 4]);
    Post::factory()->for($author, 'author')->published()->create(['title' => 'Quiet', 'views_count' => 5, 'likes_count' => 1]);
    Post::factory()->for($author, 'author')->published()->create(['title' => 'Unread', 'views_count' => 0]);
    Post::factory()->for($author, 'author')->create(['title' => 'Draft', 'views_count' => 99, 'likes_count' => 9]);
    Post::factory()->published()->create(['title' => 'Someone else', 'views_count' => 500, 'likes_count' => 50]);

    $response = $this->actingAs($author)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('totalViews', 35)
        ->where('totalLikes', 5)
        ->has('mostRead', 2)
        ->where('mostRead.0.title', 'Popular')
        ->where('mostRead.0.views_count', 30)
        ->where('mostRead.0.likes_count', 4)
        ->where('mostRead.1.title', 'Quiet')
    );
});

test('compares the views of the last 30 days with the 30 days before', function () {
    $author = User::factory()->create();
    $post = Post::factory()->for($author, 'author')->published()->create(['published_at' => '2026-07-01']);
    $views = fn (string $date, int $count) => PostDailyView::factory()->create(['post_id' => $post->id, 'date' => $date, 'views' => $count]);

    $views('2026-09-23', 4); // today, last day of the period
    $views('2026-08-25', 6); // first day of the period
    $views('2026-08-24', 10); // last day of the previous period
    $views('2026-07-26', 1); // first day of the previous period
    $views('2026-07-25', 100); // outside both
    PostDailyView::factory()->create(['date' => '2026-09-23', 'views' => 50]); // another author

    $response = $this->actingAs($author)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('periodDays', 30)
        ->where('viewsInPeriod', 10)
        ->where('viewsInPreviousPeriod', 11)
        ->has('dailyViews', 30)
        ->where('dailyViews.0', ['date' => '2026-08-25', 'views' => 6])
        ->where('dailyViews.1', ['date' => '2026-08-26', 'views' => 0])
        ->where('dailyViews.29', ['date' => '2026-09-23', 'views' => 4])
    );
});

test('counts published, draft and scheduled posts and the last publication of the author', function () {
    $author = User::factory()->create();
    Post::factory()->for($author, 'author')->published()->create(['published_at' => '2026-09-10 12:00:00']);
    Post::factory()->for($author, 'author')->published()->create(['published_at' => '2026-09-01 12:00:00']);
    Post::factory()->for($author, 'author')->published()->create(['published_at' => '2026-10-01 12:00:00']);
    Post::factory()->for($author, 'author')->count(3)->create();
    Post::factory()->published()->create(['published_at' => '2026-09-20 12:00:00']);

    $response = $this->actingAs($author)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('publishedCount', 2)
        ->where('draftCount', 3)
        ->where('scheduledCount', 1)
        ->where('lastPublishedAt', '2026-09-10T12:00:00+00:00')
    );
});

test('has no last publication before the first post goes out', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('lastPublishedAt', null)
        ->where('viewsInPeriod', 0)
        ->has('dailyViews', 30)
    );
});

test('lists the drafts of the author, last edited first, and the posts scheduled to go out next', function () {
    $author = User::factory()->create();
    Post::factory()->for($author, 'author')->create(['title' => 'Old draft', 'updated_at' => '2026-09-01 10:00:00']);
    Post::factory()->for($author, 'author')->create(['title' => 'Fresh draft', 'updated_at' => '2026-09-23 10:00:00']);
    Post::factory()->for($author, 'author')->published()->create(['title' => 'Later', 'published_at' => '2026-10-20 12:00:00']);
    Post::factory()->for($author, 'author')->published()->create(['title' => 'Sooner', 'published_at' => '2026-10-01 12:00:00']);
    Post::factory()->for($author, 'author')->published()->create(['title' => 'Already out', 'published_at' => '2026-09-01 12:00:00']);
    Post::factory()->create(['title' => 'Someone else draft']);

    $response = $this->actingAs($author)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('drafts', 2)
        ->where('drafts.0.title', 'Fresh draft')
        ->where('drafts.1.title', 'Old draft')
        ->has('scheduled', 2)
        ->where('scheduled.0.title', 'Sooner')
        ->where('scheduled.1.title', 'Later')
    );
});
