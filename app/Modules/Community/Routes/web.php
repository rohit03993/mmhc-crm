<?php

use App\Modules\Community\Controllers\CommentController;
use App\Modules\Community\Controllers\EventController;
use App\Modules\Community\Controllers\PostController;
use App\Modules\Community\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('community')->name('community.')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store')->middleware('role:admin,nurse,caregiver');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::post('/posts/{post}/reactions/toggle', [ReactionController::class, 'toggle'])->name('reactions.toggle');
    Route::post('/posts/{post}/events/interest', [EventController::class, 'setInterest'])->name('events.interest');
});

