<?php

use App\Http\Controllers\Blog\BlogFeedController;
use App\Http\Controllers\Blog\BlogPostController;
use App\Http\Controllers\Blog\BlogPrivacyController;
use App\Http\Controllers\Blog\BlogSearchController;
use App\Http\Controllers\Blog\BlogTagController;
use App\Http\Controllers\Blog\SeoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostImageController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin panel (everything under /admin; Fortify uses the same prefix)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('tags', TagController::class)->except('show');
    Route::patch('tags/{tag}/restore', [TagController::class, 'restore'])
        ->withTrashed()
        ->name('tags.restore');

    Route::resource('readings', ReadingController::class)->except('show');
    Route::patch('readings/{reading}/restore', [ReadingController::class, 'restore'])
        ->withTrashed()
        ->name('readings.restore');

    Route::post('posts/images', [PostImageController::class, 'store'])->name('posts.images.store');
    Route::resource('posts', PostController::class)->except('show');
    Route::patch('posts/{post}/restore', [PostController::class, 'restore'])
        ->withTrashed()
        ->name('posts.restore');
    Route::get('posts/{post}/preview', [PostController::class, 'preview'])->name('posts.preview');
    Route::patch('posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
    Route::patch('posts/{post}/unpublish', [PostController::class, 'unpublish'])->name('posts.unpublish');
});

Route::prefix('admin')->group(__DIR__.'/settings.php');

/*
|--------------------------------------------------------------------------
| Public blog
|--------------------------------------------------------------------------
*/

Route::get('/', [BlogPostController::class, 'index'])->name('home');
Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('feed', [BlogFeedController::class, 'index'])->name('blog.feed');
Route::get('search', [BlogSearchController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('blog.search');
Route::get('tags/{tag:slug}', [BlogTagController::class, 'show'])->name('blog.tags.show');
Route::get('privacidade', [BlogPrivacyController::class, 'show'])->name('blog.privacy');

// Addresses used before posts moved to the root and the panel to /admin.
Route::permanentRedirect('dashboard', '/admin');
Route::permanentRedirect('login', '/admin/login');
Route::permanentRedirect('blog', '/');
Route::permanentRedirect('blog/feed', '/feed');
Route::get('blog/tags/{slug}', fn (string $slug) => redirect()->route('blog.tags.show', $slug, 301));
Route::get('blog/{slug}', fn (string $slug) => redirect()->route('blog.posts.show', $slug, 301));

// Must stay last: any other single segment is a post slug.
Route::get('{slug}', [BlogPostController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('blog.posts.show');
