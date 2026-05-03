<?php

use App\Modules\Incentives\Controllers\IncentiveAdminController;
use Illuminate\Support\Facades\Route;

// `web` is required so session/auth work (module routes are not inside routes/web.php's automatic web group)
Route::middleware(['web', 'auth', 'role:admin'])->prefix('admin/incentive-system')->name('admin.incentive-system.')->group(function () {
    Route::get('/preview', [IncentiveAdminController::class, 'preview'])->name('preview');
});
