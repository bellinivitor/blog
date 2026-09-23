<?php

use App\Http\Controllers\Blog\BlogPostController;
use App\Http\Controllers\Blog\BlogSearchController;
use App\Http\Controllers\Blog\BlogTagController;
use App\Http\Controllers\Blog\SeoController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostImageController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogPostController::class, 'index'])->name('home');
Route::permanentRedirect('blog', '/');
Route::get('sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::get('blog/search', [BlogSearchController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('blog.search');
Route::get('blog/tags/{tag:slug}', [BlogTagController::class, 'show'])->name('blog.tags.show');
Route::get('blog/{slug}', [BlogPostController::class, 'show'])->name('blog.posts.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('tags', TagController::class)->except('show');
    Route::patch('tags/{tag}/restore', [TagController::class, 'restore'])
        ->withTrashed()
        ->name('tags.restore');

    Route::post('posts/images', [PostImageController::class, 'store'])->name('posts.images.store');
    Route::resource('posts', PostController::class)->except('show');
    Route::patch('posts/{post}/restore', [PostController::class, 'restore'])
        ->withTrashed()
        ->name('posts.restore');
    Route::patch('posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
    Route::patch('posts/{post}/unpublish', [PostController::class, 'unpublish'])->name('posts.unpublish');
});

require __DIR__.'/settings.php';
