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

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);           
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);    
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout'); 
});

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
    Route::post('classes/{schoolClass}/import-students', [SchoolClassController::class, 'importStudents'])
        ->name('classes.import-students');
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
    Route::get('exams/{exam}/leaderboard', [ExamController::class, 'leaderboard'])->name('exams.leaderboard');
    Route::get('exams/{exam}/gradebook.csv', [ExamController::class, 'exportGradebook'])->name('exams.export-gradebook');
    Route::get('exams/{exam}/analytics', [ExamController::class, 'analytics'])->name('exams.analytics');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/sessions', [\App\Http\Controllers\Web\Student\SessionController::class, 'index'])
        ->name('sessions.index'); 
    Route::post('/sessions/{examSession}/join', [\App\Http\Controllers\Web\Student\SessionController::class, 'join'])
        ->name('sessions.join');

    Route::get('/submissions/{submission}/take', [\App\Http\Controllers\Web\Student\SubmissionController::class, 'take'])
        ->name('submissions.take');
    Route::put('/submissions/{submission}/answers', [\App\Http\Controllers\Web\Student\SubmissionController::class, 'saveAnswers'])
        ->name('submissions.answers'); 
    Route::post('/submissions/{submission}/submit', [\App\Http\Controllers\Web\Student\SubmissionController::class, 'submit'])
        ->name('submissions.submit');
    Route::get('/submissions/{submission}/score', [\App\Http\Controllers\Web\Student\SubmissionController::class, 'score'])
        ->name('submissions.score');

    Route::get('/exams/{exam}/leaderboard', [\App\Http\Controllers\Web\Student\LeaderboardController::class, 'show'])
        ->name('exams.leaderboard'); 
});

Route::middleware(['auth', 'role:teacher', 'lead_teacher'])->prefix('admin')->group(function () {
    Route::get('/teachers', [\App\Http\Controllers\Web\Admin\TeacherAdminController::class, 'index'])
        ->name('admin.teachers.index');
    Route::post('/teachers', [\App\Http\Controllers\Web\Admin\TeacherAdminController::class, 'store'])
        ->name('admin.teachers.store');
    Route::post('/teachers/{teacher}/disable', [\App\Http\Controllers\Web\Admin\TeacherAdminController::class, 'disable'])
        ->name('admin.teachers.disable');
    Route::post('/teachers/{teacher}/enable', [\App\Http\Controllers\Web\Admin\TeacherAdminController::class, 'enable'])
        ->name('admin.teachers.enable');
    Route::delete('/teachers/{teacher}', [\App\Http\Controllers\Web\Admin\TeacherAdminController::class, 'destroy'])
        ->name('admin.teachers.destroy');
    Route::post('/users/{user}/reset-password', [\App\Http\Controllers\Web\Admin\TeacherAdminController::class, 'resetPassword'])
        ->name('admin.users.reset-password');
});
