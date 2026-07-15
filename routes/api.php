<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClassController;
use App\Http\Controllers\Api\V1\ExamController;
use App\Http\Controllers\Api\V1\SubmissionController;
use App\Http\Controllers\Api\V1\LeaderboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Sanctum token auth — student-facing mobile app)
|--------------------------------------------------------------------------
| Versioned under /api/v1 per SRS section 4.4. Every route below requires
| a valid Sanctum bearer token except login.
*/

Route::prefix('v1')->group(function () {

    // --- Auth (QES-9, QES-10) ---
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']); // QES-12
        Route::get('/me', [AuthController::class, 'me']);

        // Class joining — QES-16
        Route::post('/classes/join', [ClassController::class, 'join']);
        Route::get('/classes', [ClassController::class, 'mine']);

        // Exam listing/taking — QES-27 to QES-33
        Route::get('/exams', [ExamController::class, 'index']);              // FR-4.1
        Route::get('/exams/{exam}', [ExamController::class, 'show']);        // FR-4.2

        Route::post('/exams/{exam}/start', [SubmissionController::class, 'start']);          // creates a Submission
        Route::put('/submissions/{submission}/answers', [SubmissionController::class, 'saveAnswers']); // autosave, FR-4.5
        Route::post('/submissions/{submission}/submit', [SubmissionController::class, 'submit']);      // FR-4.6, triggers GradingService

        // Scores & leaderboard — QES-36, QES-39, QES-42
        Route::get('/submissions/{submission}/score', [SubmissionController::class, 'score']);
        Route::get('/exams/{exam}/leaderboard', [LeaderboardController::class, 'show']);
        Route::get('/classes/{schoolClass}/leaderboard', [LeaderboardController::class, 'classAggregate']); // Could, QES-42
    });
});
