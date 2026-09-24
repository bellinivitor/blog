<?php

use App\Models\Post\Post;
use App\Models\Reading\Reading;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

describe('index', function () {
    test('guests are redirected to the login page', function () {
        $response = $this->get(route('readings.index'));

        $response->assertRedirect(route('login'));
    });

    test('lists active readings newest first', function () {
        Reading::factory()->create(['title' => 'Older', 'created_at' => now()->subDay()]);
        Reading::factory()->create(['title' => 'Newer']);
        Reading::factory()->trashed()->create(['title' => 'Archived']);

        $response = $this->actingAs(User::factory()->create())->get(route('readings.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('readings/Index')
            ->has('readings.data', 2)
            ->where('readings.data.0.title', 'Newer')
            ->where('readings.data.1.title', 'Older')
            ->where('filters', ['search' => null, 'trashed' => false])
        );
    });

    test('shows how many posts cite each reading and counts active and trashed readings', function () {
        $cited = Reading::factory()->create(['title' => 'Cited', 'created_at' => now()->subDay()]);
        Reading::factory()->create(['title' => 'Not cited']);
        Reading::factory()->trashed()->create();
        Post::factory()->create(['content' => "See [the book](leitura:{$cited->id}) and [again](leitura:{$cited->id})."]);
        Post::factory()->create(['content' => "Also [this](leitura:{$cited->id})."]);
        Post::factory()->trashed()->create(['content' => "Gone [cite](leitura:{$cited->id})."]);
        Post::factory()->create(['content' => "A different [reading](leitura:{$cited->id}9)."]);

        $response = $this->actingAs(User::factory()->create())->get(route('readings.index'));

        $response->assertInertia(fn (Assert $page) => $page
            ->where('readings.data.0.title', 'Not cited')
            ->where('readings.data.0.citations_count', 0)
            ->where('readings.data.1.title', 'Cited')
            ->where('readings.data.1.citations_count', 2)
            ->where('counts', ['active' => 2, 'trashed' => 1])
        );
    });

    test('filters readings by title', function () {
        Reading::factory()->create(['title' => 'Domain-Driven Design']);
        Reading::factory()->create(['title' => 'Refactoring']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('readings.index', ['search' => 'domain design']));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('readings.data', 1)
            ->where('readings.data.0.title', 'Domain-Driven Design')
        );
    });

    test('lists only trashed readings when requested', function () {
        Reading::factory()->create(['title' => 'Refactoring']);
        Reading::factory()->trashed()->create(['title' => 'Archived']);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('readings.index', ['trashed' => 1]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('readings.data', 1)
            ->where('readings.data.0.title', 'Archived')
        );
    });
});

describe('store', function () {
    test('creates a reading', function () {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('readings.store'), ['title' => 'Refactoring', 'url' => 'https://martinfowler.com/books/refactoring.html']);

        $response->assertRedirect(route('readings.index'));
        $this->assertDatabaseHas('readings', ['title' => 'Refactoring', 'url' => 'https://martinfowler.com/books/refactoring.html']);
    });

    test('requires a title and a link', function () {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('readings.store'), []);

        $response->assertInvalid(['title' => 'required', 'url' => 'required']);
        $this->assertDatabaseCount('readings', 0);
    });

    test('accepts only http and https links', function (string $url) {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('readings.store'), ['title' => 'Refactoring', 'url' => $url]);

        $response->assertInvalid(['url']);
        $this->assertDatabaseCount('readings', 0);
    })->with([
        'javascript' => 'javascript:alert(1)',
        'ftp' => 'ftp://example.com/book.pdf',
        'not a url' => 'refactoring',
    ]);
});

describe('update', function () {
    test('updates the title and link', function () {
        $reading = Reading::factory()->create();

        $response = $this->actingAs(User::factory()->create())
            ->put(route('readings.update', $reading), ['title' => 'Refactoring, 2nd edition', 'url' => 'https://example.com/2nd']);

        $response->assertRedirect(route('readings.index'));
        expect($reading->refresh())
            ->title->toBe('Refactoring, 2nd edition')
            ->url->toBe('https://example.com/2nd');
    });

    test('validates the link on update too', function () {
        $reading = Reading::factory()->create(['url' => 'https://example.com']);

        $response = $this->actingAs(User::factory()->create())
            ->put(route('readings.update', $reading), ['title' => 'Refactoring', 'url' => 'javascript:alert(1)']);

        $response->assertInvalid(['url']);
        expect($reading->refresh()->url)->toBe('https://example.com');
    });
});

describe('destroy', function () {
    test('moves the reading to the trash', function () {
        $reading = Reading::factory()->create();

        $response = $this->actingAs(User::factory()->create())->delete(route('readings.destroy', $reading));

        $response->assertRedirect(route('readings.index'));
        $this->assertSoftDeleted($reading);
    });
});

describe('restore', function () {
    test('restores a trashed reading', function () {
        $reading = Reading::factory()->trashed()->create();

        $response = $this->actingAs(User::factory()->create())->patch(route('readings.restore', $reading));

        $response->assertRedirect(route('readings.index'));
        $this->assertNotSoftDeleted($reading);
    });
});

describe('search', function () {
    test('guests cannot search readings', function () {
        $response = $this->getJson(route('readings.search', ['search' => 'design']));

        $response->assertUnauthorized();
    });

    test('returns active readings matching the title, newest first', function () {
        Reading::factory()->create(['title' => 'Domain-Driven Design', 'created_at' => now()->subDay()]);
        Reading::factory()->create(['title' => 'A Philosophy of Software Design', 'url' => 'https://example.com/aposd']);
        Reading::factory()->create(['title' => 'Refactoring']);
        Reading::factory()->trashed()->create(['title' => 'Design Patterns']);

        $response = $this->actingAs(User::factory()->create())
            ->getJson(route('readings.search', ['search' => 'design']));

        $response->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.title', 'A Philosophy of Software Design')
            ->assertJsonPath('0.url', 'https://example.com/aposd')
            ->assertJsonPath('1.title', 'Domain-Driven Design');
    });

    test('returns at most eight readings', function () {
        Reading::factory()->count(10)->create();

        $response = $this->actingAs(User::factory()->create())->getJson(route('readings.search'));

        $response->assertJsonCount(8);
    });
});
