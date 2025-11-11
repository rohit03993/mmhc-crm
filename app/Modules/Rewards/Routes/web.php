<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Rewards\Controllers\RewardController;

Route::middleware(['web', 'auth', 'role:caregiver,nurse'])
    ->prefix('rewards')
    ->name('rewards.')
    ->group(function () {
        Route::get('/', [RewardController::class, 'index'])->name('index');
        Route::get('/create', [RewardController::class, 'create'])->name('create');
        Route::post('/', [RewardController::class, 'store'])->name('store');
    });

Route::middleware(['web', 'auth', 'role:admin'])
    ->prefix('admin/rewards')
    ->name('admin.rewards.')
    ->group(function () {
        Route::get('/', [RewardController::class, 'adminIndex'])->name('index');
    });

