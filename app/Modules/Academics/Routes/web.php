<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Academics\Controllers\AcademicsDashboardController;
use App\Modules\Academics\Controllers\InstitutionController;
use App\Modules\Academics\Controllers\BatchController;
use App\Modules\Academics\Controllers\SubjectController;
use App\Modules\Academics\Controllers\TopicController;
use App\Modules\Academics\Controllers\AssignmentController;
use App\Modules\Academics\Controllers\AttendanceController;
use App\Modules\Academics\Controllers\ReportController;
use App\Modules\Academics\Controllers\SubmissionController;

/*
|--------------------------------------------------------------------------
| Academics Module Routes - All under /academics
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->prefix('academics')->name('academics.')->group(function () {
    Route::get('/', [AcademicsDashboardController::class, 'index'])->name('dashboard');

    // Super Admin: Institution management
    Route::middleware(['role:super_admin'])->prefix('institutions')->name('institutions.')->group(function () {
        Route::get('/', [InstitutionController::class, 'index'])->name('index');
        Route::get('/create', [InstitutionController::class, 'create'])->name('create');
        Route::post('/', [InstitutionController::class, 'store'])->name('store');
        Route::get('/{institution}/edit', [InstitutionController::class, 'edit'])->name('edit');
        Route::put('/{institution}', [InstitutionController::class, 'update'])->name('update');
        Route::delete('/{institution}', [InstitutionController::class, 'destroy'])->name('destroy');
    });

    // Batch management (Super Admin + Institution Admin)
    Route::middleware(['role:super_admin,institution_admin'])->prefix('batches')->name('batches.')->group(function () {
        Route::get('/', [BatchController::class, 'index'])->name('index');
        Route::get('/create', [BatchController::class, 'create'])->name('create');
        Route::post('/', [BatchController::class, 'store'])->name('store');
        Route::get('/{batch}/edit', [BatchController::class, 'edit'])->name('edit');
        Route::put('/{batch}', [BatchController::class, 'update'])->name('update');
        Route::post('/{batch}/assignments', [BatchController::class, 'updateAssignments'])->name('assignments.update');
        Route::delete('/{batch}', [BatchController::class, 'destroy'])->name('destroy');
    });

    // Subject management (Super Admin + Institution Admin)
    Route::middleware(['role:super_admin,institution_admin'])->prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('index');
        Route::get('/create', [SubjectController::class, 'create'])->name('create');
        Route::post('/', [SubjectController::class, 'store'])->name('store');
        Route::get('/{subject}/edit', [SubjectController::class, 'edit'])->name('edit');
        Route::put('/{subject}', [SubjectController::class, 'update'])->name('update');
        Route::post('/{subject}/faculty', [SubjectController::class, 'updateFaculty'])->name('faculty.update');
        Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('destroy');
    });

    // Topic management (Super Admin + Institution Admin + Faculty)
    Route::middleware(['role:super_admin,institution_admin,faculty'])->prefix('topics')->name('topics.')->group(function () {
        Route::get('/', [TopicController::class, 'index'])->name('index');
        Route::get('/create', [TopicController::class, 'create'])->name('create');
        Route::post('/', [TopicController::class, 'store'])->name('store');
        Route::get('/{topic}/edit', [TopicController::class, 'edit'])->name('edit');
        Route::put('/{topic}', [TopicController::class, 'update'])->name('update');
        Route::delete('/{topic}', [TopicController::class, 'destroy'])->name('destroy');
    });

    // Assignment management (Super Admin + Institution Admin + Faculty)
    Route::middleware(['role:super_admin,institution_admin,faculty'])->prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [AssignmentController::class, 'index'])->name('index');
        Route::get('/create', [AssignmentController::class, 'create'])->name('create');
        Route::post('/', [AssignmentController::class, 'store'])->name('store');
        Route::get('/{assignment}', [AssignmentController::class, 'show'])->name('show');
        Route::get('/{assignment}/edit', [AssignmentController::class, 'edit'])->name('edit');
        Route::put('/{assignment}', [AssignmentController::class, 'update'])->name('update');
        Route::get('/{assignment}/download/{index}', [AssignmentController::class, 'downloadAttachment'])->name('download')->where('index', '[0-9]+');
        Route::post('/{assignment}/remove-attachment', [AssignmentController::class, 'removeAttachment'])->name('remove-attachment');
        Route::get('/{assignment}/submissions', [SubmissionController::class, 'forAssignment'])->name('submissions');
        Route::delete('/{assignment}', [AssignmentController::class, 'destroy'])->name('destroy');
    });

    // Reports (Super Admin, Institution Admin, Faculty)
    Route::middleware(['role:super_admin,institution_admin,faculty'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/show', [ReportController::class, 'show'])->name('show');
        Route::get('/download', [ReportController::class, 'download'])->name('download');
    });

    // Attendance: Mark (Super Admin, Institution Admin, Faculty)
    Route::middleware(['role:super_admin,institution_admin,faculty'])->prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/mark', [AttendanceController::class, 'mark'])->name('mark');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
    });

    // Student: My Assignments & Submit
    Route::middleware(['role:student'])->group(function () {
        Route::get('/my-attendance', [AttendanceController::class, 'myAttendance'])->name('attendance.my');
        Route::get('/my-assignments', [SubmissionController::class, 'index'])->name('my-assignments');
        Route::get('/assignments/{assignment}/submit', [SubmissionController::class, 'create'])->name('submit.form');
        Route::post('/assignments/{assignment}/submit', [SubmissionController::class, 'store'])->name('submit.store');
    });

    // Submission download (student own / faculty or admin for any)
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
});
