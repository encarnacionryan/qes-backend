<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\SchoolClassController;
use App\Http\Controllers\Web\ExamController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return redirect(auth()->user()->role === 'teacher' ? route('dashboard') : route('student.sessions.index'));
});

// --- Guest ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);           
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);     
});


Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout'); 
});

// --- Authenticated (teacher) ---
Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');


    Route::resource('classes', SchoolClassController::class)
        ->except(['show'])
        ->parameters(['classes' => 'schoolClass']);
    Route::get('classes/{schoolClass}', [SchoolClassController::class, 'show'])->name('classes.show');
    Route::delete('classes/{schoolClass}/students/{student}', [SchoolClassController::class, 'removeStudent'])
        ->name('classes.students.remove'); 
    Route::post('classes/{schoolClass}/archive', [SchoolClassController::class, 'archive'])
        ->name('classes.archive'); 

    Route::resource('exams', ExamController::class);
    Route::post('exams/{exam}/duplicate', [ExamController::class, 'duplicate'])->name('exams.duplicate'); 
    Route::post('exams/{exam}/questions', [\App\Http\Controllers\Web\QuestionController::class, 'store'])
        ->name('questions.store');
    Route::put('exams/{exam}/questions/{question}', [\App\Http\Controllers\Web\QuestionController::class, 'update'])
        ->name('questions.update');
    Route::delete('exams/{exam}/questions/{question}', [\App\Http\Controllers\Web\QuestionController::class, 'destroy'])
        ->name('questions.destroy');
    Route::post('exams/{exam}/questions/reorder', [\App\Http\Controllers\Web\QuestionController::class, 'reorder'])
        ->name('questions.reorder');
    
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
