<?php

use App\Models\User;
use Database\Seeders\OwnerUserSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    config([
        'blog.owner.email' => 'owner@example.com',
        'blog.owner.password' => 'owner-secret',
    ]);
});

test('seeds the verified owner account with the login from the environment', function () {
    $this->seed(OwnerUserSeeder::class);

    $owner = User::query()->where('email', 'owner@example.com')->sole();

    expect($owner->name)->toBe(config('blog.author'))
        ->and($owner->email_verified_at)->not->toBeNull()
        ->and(Hash::check('owner-secret', $owner->password))->toBeTrue();
});

test('can run again without duplicating the owner or resetting a changed password', function () {
    $this->seed(OwnerUserSeeder::class);
    User::query()->where('email', 'owner@example.com')->sole()->update(['password' => 'a-new-password']);

    $this->seed(OwnerUserSeeder::class);

    $owner = User::query()->where('email', 'owner@example.com')->sole();
    expect(Hash::check('a-new-password', $owner->password))->toBeTrue();
});

test('refuses to seed without the owner login in the environment', function (string $missingKey) {
    config([$missingKey => null]);

    $this->seed(OwnerUserSeeder::class);
})->with(['blog.owner.email', 'blog.owner.password'])
    ->throws(RuntimeException::class, 'BLOG_OWNER_EMAIL');
