<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('tags', TagController::class)->except('show');
    Route::patch('tags/{tag}/restore', [TagController::class, 'restore'])
        ->withTrashed()
        ->name('tags.restore');

    Route::resource('posts', PostController::class)->except('show');
    Route::patch('posts/{post}/restore', [PostController::class, 'restore'])
        ->withTrashed()
        ->name('posts.restore');
});

require __DIR__.'/settings.php';
