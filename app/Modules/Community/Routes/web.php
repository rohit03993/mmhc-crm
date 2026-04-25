<?php

use App\Modules\Community\Controllers\CommentController;
use App\Modules\Community\Controllers\EventController;
use App\Modules\Community\Controllers\NotificationController;
use App\Modules\Community\Controllers\PostController;
use App\Modules\Community\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('community')->name('community.')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store')->middleware('role:admin,nurse,caregiver');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/pin', [PostController::class, 'togglePin'])->name('posts.pin')->middleware('role:admin');

    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store')->middleware('throttle:20,1');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::post('/posts/{post}/reactions', [ReactionController::class, 'react'])->name('reactions.react')->middleware('throttle:30,1');
    Route::post('/posts/{post}/events/interest', [EventController::class, 'setInterest'])->name('events.interest');
    Route::post('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

