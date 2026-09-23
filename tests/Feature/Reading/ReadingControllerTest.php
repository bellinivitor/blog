<?php

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

describe('create', function () {
    test('renders the create page', function () {
        $response = $this->actingAs(User::factory()->create())->get(route('readings.create'));

        $response->assertInertia(fn (Assert $page) => $page->component('readings/Create'));
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

describe('edit', function () {
    test('renders the edit page with the reading', function () {
        $reading = Reading::factory()->create(['title' => 'Refactoring', 'url' => 'https://example.com']);

        $response = $this->actingAs(User::factory()->create())->get(route('readings.edit', $reading));

        $response->assertInertia(fn (Assert $page) => $page
            ->component('readings/Edit')
            ->where('reading.id', $reading->id)
            ->where('reading.title', 'Refactoring')
            ->where('reading.url', 'https://example.com')
        );
    });
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
