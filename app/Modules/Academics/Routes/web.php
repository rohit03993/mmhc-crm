<?php

use App\Modules\Academics\Controllers\AcademicsDashboardController;
use App\Modules\Academics\Controllers\AcademicsPeopleController;
use App\Modules\Academics\Controllers\AssignmentController;
use App\Modules\Academics\Controllers\AttendanceController;
use App\Modules\Academics\Controllers\BatchController;
use App\Modules\Academics\Controllers\ExamController;
use App\Modules\Academics\Controllers\FacultyController;
use App\Modules\Academics\Controllers\InstitutionController;
use App\Modules\Academics\Controllers\ReportController;
use App\Modules\Academics\Controllers\SubjectController;
use App\Modules\Academics\Controllers\EnrollmentController;
use App\Modules\Academics\Controllers\MentorshipController;
use App\Modules\Academics\Controllers\OpenClassroomAssignmentController;
use App\Modules\Academics\Controllers\OpenClassroomController;
use App\Modules\Academics\Controllers\StudentController;
use App\Modules\Academics\Controllers\SubmissionController;
use App\Modules\Academics\Controllers\TopicController;
use App\Modules\Academics\Controllers\TopicResourceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Academics Module Routes - All under /academics
| Platform admin: institutes + read-only college overview only.
| Institute admin: all college operations for their institution.
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->prefix('academics')->name('academics.')->group(function () {
    Route::middleware(['role:admin,institution_admin,faculty,student,nurse,caregiver'])->prefix('open-classrooms')->name('open-classrooms.')->group(function () {
        Route::get('/', [OpenClassroomController::class, 'index'])->name('index');
        Route::get('/create', [OpenClassroomController::class, 'create'])->name('create');
        Route::post('/', [OpenClassroomController::class, 'store'])->name('store');
        Route::get('/{openClassroom}', [OpenClassroomController::class, 'show'])->name('show')->whereNumber('openClassroom');
        Route::get('/{openClassroom}/edit', [OpenClassroomController::class, 'edit'])->name('edit')->whereNumber('openClassroom');
        Route::put('/{openClassroom}', [OpenClassroomController::class, 'update'])->name('update')->whereNumber('openClassroom');
        Route::post('/{openClassroom}/join', [OpenClassroomController::class, 'join'])->name('join')->whereNumber('openClassroom');
        Route::post('/{openClassroom}/leave', [OpenClassroomController::class, 'leave'])->name('leave')->whereNumber('openClassroom');
        Route::post('/{openClassroom}/resources', [OpenClassroomController::class, 'storeResource'])->name('resources.store')->whereNumber('openClassroom');
        Route::delete('/{openClassroom}/resources/{resource}', [OpenClassroomController::class, 'destroyResource'])->name('resources.destroy')->whereNumber(['openClassroom', 'resource']);
        Route::get('/{openClassroom}/resources/{resource}/download', [OpenClassroomController::class, 'downloadResource'])->name('resources.download')->whereNumber(['openClassroom', 'resource']);
        Route::post('/{openClassroom}/assignments', [OpenClassroomAssignmentController::class, 'store'])->name('assignments.store')->whereNumber('openClassroom');
        Route::get('/{openClassroom}/assignments/{assignment}', [OpenClassroomAssignmentController::class, 'show'])->name('assignments.show')->whereNumber(['openClassroom', 'assignment']);
        Route::get('/{openClassroom}/assignments/{assignment}/submissions', [OpenClassroomAssignmentController::class, 'submissions'])->name('assignments.submissions')->whereNumber(['openClassroom', 'assignment']);
        Route::get('/{openClassroom}/assignments/{assignment}/submit', [OpenClassroomAssignmentController::class, 'submitForm'])->name('assignments.submit')->whereNumber(['openClassroom', 'assignment']);
        Route::post('/{openClassroom}/assignments/{assignment}/submit', [OpenClassroomAssignmentController::class, 'submitStore'])->name('assignments.submit.store')->whereNumber(['openClassroom', 'assignment']);
        Route::get('/{openClassroom}/assignments/{assignment}/download/{index}', [OpenClassroomAssignmentController::class, 'downloadAttachment'])->name('assignments.download')->whereNumber(['openClassroom', 'assignment'])->where('index', '[0-9]+');
        Route::get('/{openClassroom}/submissions/{submission}/download', [OpenClassroomAssignmentController::class, 'downloadSubmission'])->name('submissions.download')->whereNumber(['openClassroom', 'submission']);
    });

    Route::middleware(['role:admin,institution_admin,faculty,student'])->group(function () {
        Route::get('/', [AcademicsDashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware(['role:institution_admin,faculty,student'])->group(function () {
        Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show')->whereNumber('exam');
        Route::get('/exams/{exam}/attempts/{attempt}/result', [ExamController::class, 'result'])->name('exams.result')->whereNumber(['exam', 'attempt']);
    });

    Route::middleware(['role:institution_admin,faculty'])->group(function () {
        Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
        Route::get('/exams/{exam}/attempts', [ExamController::class, 'attempts'])->name('exams.attempts')->whereNumber('exam');
        Route::get('/exams/{exam}/attempts/export', [ExamController::class, 'exportAttempts'])->name('exams.attempts.export')->whereNumber('exam');
        Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('/exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit')->whereNumber('exam');
        Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update')->whereNumber('exam');
        Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy')->whereNumber('exam');
        Route::post('/exams/{exam}/questions', [ExamController::class, 'storeQuestion'])->name('exams.questions.store')->whereNumber('exam');
        Route::post('/exams/{exam}/questions/{question}/reorder', [ExamController::class, 'reorderQuestion'])->name('exams.questions.reorder')->whereNumber(['exam', 'question']);
        Route::delete('/exams/{exam}/questions/{question}', [ExamController::class, 'destroyQuestion'])->name('exams.questions.destroy')->whereNumber(['exam', 'question']);
    });

    Route::middleware(['role:student', 'student.enrollment.approved'])->group(function () {
        Route::get('/my-learning-resources', [TopicResourceController::class, 'studentTopicsIndex'])->name('learning-resources');
        Route::get('/my-attendance', [AttendanceController::class, 'myAttendance'])->name('attendance.my');
        Route::get('/my-assignments', [SubmissionController::class, 'index'])->name('my-assignments');
        Route::get('/my-assignments/{assignment}', [SubmissionController::class, 'show'])->name('my-assignments.show')->whereNumber('assignment');
        Route::get('/assignments/{assignment}/submit', [SubmissionController::class, 'create'])->name('submit.form');
        Route::post('/assignments/{assignment}/submit', [SubmissionController::class, 'store'])->name('submit.store');
        Route::get('/assignments/{assignment}/download/{index}', [AssignmentController::class, 'downloadAttachment'])->name('assignments.material-download')->whereNumber('assignment')->where('index', '[0-9]+');
        Route::get('/topic-library/{topic}', [TopicResourceController::class, 'studentLibrary'])->name('topics.student-library')->whereNumber('topic');
    });

    Route::middleware(['role:student'])->group(function () {
        Route::post('/exams/{exam}/start', [ExamController::class, 'startAttempt'])->name('exams.start')->whereNumber('exam');
        Route::get('/exams/{exam}/take/{attempt}', [ExamController::class, 'take'])->name('exams.take')->whereNumber(['exam', 'attempt']);
        Route::post('/exams/{exam}/take/{attempt}', [ExamController::class, 'submitAttempt'])->name('exams.submit')->whereNumber(['exam', 'attempt']);
    });

    Route::middleware(['role:admin,institution_admin,faculty'])->group(function () {
        Route::get('/institutions/{institution}', [InstitutionController::class, 'show'])
            ->name('institutions.show')
            ->whereNumber('institution');
        Route::get('/people/{user}', [AcademicsPeopleController::class, 'show'])->name('people.show')->whereNumber('user');
    });

    Route::middleware(['role:admin'])->prefix('institutions')->name('institutions.')->group(function () {
        Route::get('/', [InstitutionController::class, 'index'])->name('index');
        Route::get('/create', [InstitutionController::class, 'create'])->name('create');
        Route::post('/', [InstitutionController::class, 'store'])->name('store');
        Route::get('/{institution}/edit', [InstitutionController::class, 'edit'])->name('edit');
        Route::put('/{institution}', [InstitutionController::class, 'update'])->name('update');
        Route::delete('/{institution}', [InstitutionController::class, 'destroy'])->name('destroy');
    });

    Route::middleware(['role:institution_admin'])->prefix('batches')->name('batches.')->group(function () {
        Route::get('/', [BatchController::class, 'index'])->name('index');
        Route::get('/create', [BatchController::class, 'create'])->name('create');
        Route::post('/', [BatchController::class, 'store'])->name('store');
        Route::get('/{batch}/edit', [BatchController::class, 'edit'])->name('edit');
        Route::put('/{batch}', [BatchController::class, 'update'])->name('update');
        Route::post('/{batch}/assignments', [BatchController::class, 'updateAssignments'])->name('assignments.update');
        Route::delete('/{batch}', [BatchController::class, 'destroy'])->name('destroy');
    });

    Route::middleware(['role:institution_admin'])->prefix('faculty')->name('faculty.')->group(function () {
        Route::get('/', [FacultyController::class, 'index'])->name('index');
        Route::get('/create', [FacultyController::class, 'create'])->name('create');
        Route::post('/', [FacultyController::class, 'store'])->name('store');
    });

    Route::middleware(['role:institution_admin'])->prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/create', [StudentController::class, 'create'])->name('create');
        Route::post('/', [StudentController::class, 'store'])->name('store');
    });

    Route::middleware(['role:institution_admin'])->prefix('enrollments')->name('enrollments.')->group(function () {
        Route::get('/', [EnrollmentController::class, 'index'])->name('index');
        Route::get('/{application}', [EnrollmentController::class, 'show'])->name('show')->whereNumber('application');
        Route::post('/{application}/approve', [EnrollmentController::class, 'approve'])->name('approve')->whereNumber('application');
        Route::post('/{application}/reject', [EnrollmentController::class, 'reject'])->name('reject')->whereNumber('application');
    });

    Route::middleware(['role:student,nurse,caregiver,faculty'])->prefix('mentorship')->name('mentorship.')->group(function () {
        Route::get('/', [MentorshipController::class, 'index'])->name('index');
        Route::get('/profile/{user}', [MentorshipController::class, 'profile'])->name('profile')->whereNumber('user');
        Route::get('/browse', [MentorshipController::class, 'browse'])->name('browse');
        Route::post('/request', [MentorshipController::class, 'request'])->name('request');
        Route::post('/{mentorship}/respond', [MentorshipController::class, 'respond'])->name('respond')->whereNumber('mentorship');
        Route::get('/reviews/{share}', [MentorshipController::class, 'reviewForm'])->name('reviews.show')->whereNumber('share');
        Route::post('/reviews/{share}', [MentorshipController::class, 'reviewStore'])->name('reviews.store')->whereNumber('share');
    });

    Route::middleware(['role:institution_admin'])->prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('index');
        Route::get('/create', [SubjectController::class, 'create'])->name('create');
        Route::post('/', [SubjectController::class, 'store'])->name('store');
        Route::get('/{subject}/edit', [SubjectController::class, 'edit'])->name('edit');
        Route::put('/{subject}', [SubjectController::class, 'update'])->name('update');
        Route::post('/{subject}/faculty', [SubjectController::class, 'updateFaculty'])->name('faculty.update');
        Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('destroy');
    });

    Route::middleware(['role:institution_admin,faculty'])->prefix('topics')->name('topics.')->group(function () {
        Route::get('/', [TopicController::class, 'index'])->name('index');
        Route::get('/create', [TopicController::class, 'create'])->name('create');
        Route::post('/', [TopicController::class, 'store'])->name('store');
        Route::get('/{topic}/edit', [TopicController::class, 'edit'])->name('edit');
        Route::put('/{topic}', [TopicController::class, 'update'])->name('update');
        Route::delete('/{topic}', [TopicController::class, 'destroy'])->name('destroy');
        Route::get('/{topic}/resources', [TopicResourceController::class, 'index'])->name('resources.index');
        Route::get('/{topic}/resources/create', [TopicResourceController::class, 'create'])->name('resources.create');
        Route::post('/{topic}/resources', [TopicResourceController::class, 'store'])->name('resources.store');
        Route::get('/{topic}/resources/{resource}/edit', [TopicResourceController::class, 'edit'])->name('resources.edit');
        Route::put('/{topic}/resources/{resource}', [TopicResourceController::class, 'update'])->name('resources.update');
        Route::delete('/{topic}/resources/{resource}', [TopicResourceController::class, 'destroy'])->name('resources.destroy');
    });

    Route::get('/topics/{topic}/resources/{resource}/download', [TopicResourceController::class, 'download'])
        ->name('topics.resources.download')
        ->whereNumber(['topic', 'resource']);

    Route::middleware(['role:institution_admin,faculty'])->prefix('assignments')->name('assignments.')->group(function () {
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

    Route::middleware(['role:admin,institution_admin,faculty'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/show', [ReportController::class, 'show'])->name('show');
        Route::get('/download', [ReportController::class, 'download'])->name('download');
        Route::get('/student/{user}', [ReportController::class, 'studentReport'])->name('student')->whereNumber('user');
    });

    Route::middleware(['role:institution_admin,faculty'])->prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/mark', [AttendanceController::class, 'mark'])->name('mark');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
    });

    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
});
