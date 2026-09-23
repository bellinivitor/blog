<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('stores the image and returns its public url', function () {
    $image = UploadedFile::fake()->image('cover.jpg', 800, 600);

    $response = $this->actingAs(User::factory()->create())
        ->postJson(route('posts.images.store'), ['image' => $image]);

    $response->assertCreated()
        ->assertExactJson(['url' => Storage::disk('public')->url("posts/{$image->hashName()}")]);
    Storage::disk('public')->assertExists("posts/{$image->hashName()}");
});

test('returns 401 for guests', function () {
    $response = $this->postJson(route('posts.images.store'), ['image' => UploadedFile::fake()->image('cover.jpg')]);

    $response->assertUnauthorized();
    Storage::disk('public')->assertDirectoryEmpty('posts');
});

test('rejects files that are not images', function (UploadedFile $file) {
    $response = $this->actingAs(User::factory()->create())
        ->postJson(route('posts.images.store'), ['image' => $file]);

    $response->assertInvalid(['image' => 'The image field must be an image.']);
    Storage::disk('public')->assertDirectoryEmpty('posts');
})->with([
    'pdf' => fn () => UploadedFile::fake()->create('document.pdf', 10, 'application/pdf'),
    'svg' => fn () => UploadedFile::fake()->create('logo.svg', 1, 'image/svg+xml'),
]);

test('rejects images larger than 5 MB', function () {
    $response = $this->actingAs(User::factory()->create())
        ->postJson(route('posts.images.store'), ['image' => UploadedFile::fake()->image('huge.jpg')->size(5 * 1024 + 1)]);

    $response->assertInvalid(['image' => 'The image field must not be greater than 5120 kilobytes.']);
    Storage::disk('public')->assertDirectoryEmpty('posts');
});
