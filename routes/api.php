<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClassController;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\SubmissionController;
use App\Http\Controllers\Api\V1\LeaderboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Sanctum token auth — student-facing mobile app)
|--------------------------------------------------------------------------
| Versioned under /api/v1 per SRS section 4.4. Every route below requires
| a valid Sanctum bearer token except login/register.
|
| Access model update: exam access is no longer gated by class enrollment.
| Students browse and join open ExamSessions server-wide, gated only by
| session visibility (public/private) + password. See SessionController.
*/

Route::prefix('v1')->group(function () {

    // --- Auth (QES-9, QES-10, + new student self-registration) ---
    Route::post('/register', [AuthController::class, 'register']); // new
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']); // QES-12
        Route::get('/me', [AuthController::class, 'me']);

        // Class joining — QES-16. Classes remain for teacher-side rostering
        // and reporting; they no longer gate which exams a student can see.
        Route::post('/classes/join', [ClassController::class, 'join']);
        Route::get('/classes', [ClassController::class, 'mine']);

        // Exam sessions — browse (public + private, password-gated on join),
        // join, and re-fetch content mid-attempt. Replaces the old
        // class-gated /exams listing.
        Route::get('/sessions', [SessionController::class, 'index']);              // browse
        Route::post('/sessions/{examSession}/join', [SessionController::class, 'join']); // validate + create Submission
        Route::get('/sessions/{examSession}', [SessionController::class, 'show']);       // re-fetch after joining

        // Exam taking — QES-29 to QES-33
        Route::put('/submissions/{submission}/answers', [SubmissionController::class, 'saveAnswers']); // autosave, FR-4.5
        Route::post('/submissions/{submission}/submit', [SubmissionController::class, 'submit']);      // FR-4.6, triggers GradingService

        // Scores & leaderboard — QES-36, QES-39, QES-42
        Route::get('/submissions/{submission}/score', [SubmissionController::class, 'score']);
        Route::get('/exams/{exam}/leaderboard', [LeaderboardController::class, 'show']);
        Route::get('/classes/{schoolClass}/leaderboard', [LeaderboardController::class, 'classAggregate']); // Could, QES-42
    });
});
