<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\SchoolClassController;
use App\Http\Controllers\Web\ExamController;
use Illuminate\Support\Facades\Route;


// Root: send authenticated teachers to their dashboard, everyone else to login.
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
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
    Route::post('exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish'); // QES-24
    Route::post('exams/{exam}/duplicate', [ExamController::class, 'duplicate'])->name('exams.duplicate'); // QES-26

    // Results / analytics — QES-38 to QES-46 (later sprints)
    Route::get('exams/{exam}/leaderboard', [ExamController::class, 'leaderboard'])->name('exams.leaderboard');
    Route::get('exams/{exam}/analytics', [ExamController::class, 'analytics'])->name('exams.analytics');
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
