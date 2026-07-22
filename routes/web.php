<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\SchoolClassController;
use App\Http\Controllers\Web\ExamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Inertia — teacher-facing)
|--------------------------------------------------------------------------
| Session-based auth. Mirrors FR-1.x through FR-8.x from the SRS.
| Controllers referenced here are stubs to build out sprint by sprint —
| see the ticket ID noted above each group.
*/

// Root: send authenticated users to their role's home, everyone else to login.
Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect(auth()->user()->role === 'teacher' ? route('dashboard') : route('student.sessions.index'));
});

// --- Guest ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);           // QES-8
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);     // QES-7
});

// --- Authenticated (teacher) ---
Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout'); // QES-12

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Class management — QES-14 to QES-19
    // ->parameters() overrides the default {class} placeholder (singular of
    // "classes") with {schoolClass}, matching the SchoolClass $schoolClass
    // parameter used throughout SchoolClassController.
    Route::resource('classes', SchoolClassController::class)
        ->except(['show'])
        ->parameters(['classes' => 'schoolClass']);
    Route::get('classes/{schoolClass}', [SchoolClassController::class, 'show'])->name('classes.show');
    Route::delete('classes/{schoolClass}/students/{student}', [SchoolClassController::class, 'removeStudent'])
        ->name('classes.students.remove'); // QES-17
    Route::post('classes/{schoolClass}/archive', [SchoolClassController::class, 'archive'])
        ->name('classes.archive'); // QES-18

    // Exam management — QES-20 to QES-26
    Route::resource('exams', ExamController::class);
    Route::post('exams/{exam}/duplicate', [ExamController::class, 'duplicate'])->name('exams.duplicate'); // QES-26

    // Exam sessions — new: public/private, password-gated, browsable by any student
    Route::get('exams/{exam}/sessions', [\App\Http\Controllers\Web\ExamSessionController::class, 'index'])
        ->name('sessions.index');
    Route::post('exams/{exam}/sessions', [\App\Http\Controllers\Web\ExamSessionController::class, 'store'])
        ->name('sessions.store');
    Route::post('sessions/{examSession}/close', [\App\Http\Controllers\Web\ExamSessionController::class, 'close'])
        ->name('sessions.close');

    // Results / analytics — QES-38 to QES-46 (later sprints)
    Route::get('exams/{exam}/leaderboard', [ExamController::class, 'leaderboard'])->name('exams.leaderboard');
    Route::get('exams/{exam}/analytics', [ExamController::class, 'analytics'])->name('exams.analytics');
});

// --- Authenticated (student) — PWA update: students now use this same
// web app instead of a separate native app, so it works on desktop,
// tablet, and phone alike via the installable PWA shell. ---
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/sessions', [\App\Http\Controllers\Web\Student\SessionController::class, 'index'])
        ->name('sessions.index'); // browse open sessions, server-wide
    Route::post('/sessions/{examSession}/join', [\App\Http\Controllers\Web\Student\SessionController::class, 'join'])
        ->name('sessions.join');

    Route::get('/submissions/{submission}/take', [\App\Http\Controllers\Web\Student\SubmissionController::class, 'take'])
        ->name('submissions.take');
    Route::put('/submissions/{submission}/answers', [\App\Http\Controllers\Web\Student\SubmissionController::class, 'saveAnswers'])
        ->name('submissions.answers'); // autosave, FR-4.5
    Route::post('/submissions/{submission}/submit', [\App\Http\Controllers\Web\Student\SubmissionController::class, 'submit'])
        ->name('submissions.submit');
    Route::get('/submissions/{submission}/score', [\App\Http\Controllers\Web\Student\SubmissionController::class, 'score'])
        ->name('submissions.score');
});

// --- Lead teacher only — QES-47 to QES-50 ---
Route::middleware(['auth', 'role:teacher', 'lead_teacher'])->prefix('admin')->group(function () {
    Route::get('/teachers', [\App\Http\Controllers\Web\Admin\TeacherAdminController::class, 'index'])
        ->name('admin.teachers.index');
    Route::post('/teachers/{teacher}/disable', [\App\Http\Controllers\Web\Admin\TeacherAdminController::class, 'disable'])
        ->name('admin.teachers.disable');
    Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Web\Admin\TeacherAdminController::class, 'resetPassword'])
        ->name('admin.users.reset-password');
});
