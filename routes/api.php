<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClassController;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\SubmissionController;
use App\Http\Controllers\Api\V1\LeaderboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/register', [AuthController::class, 'register']); 
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']); 
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/classes', [ClassController::class, 'mine']);
        Route::get('/sessions', [SessionController::class, 'index']);              
        Route::post('/sessions/{examSession}/join', [SessionController::class, 'join']); 
        Route::get('/sessions/{examSession}', [SessionController::class, 'show']);     
        Route::put('/submissions/{submission}/answers', [SubmissionController::class, 'saveAnswers']); 
        Route::post('/submissions/{submission}/submit', [SubmissionController::class, 'submit']);   
        Route::get('/submissions/{submission}/score', [SubmissionController::class, 'score']);
        Route::get('/exams/{exam}/leaderboard', [LeaderboardController::class, 'show']);
        Route::get('/classes/{schoolClass}/leaderboard', [LeaderboardController::class, 'classAggregate']);
    });
});
