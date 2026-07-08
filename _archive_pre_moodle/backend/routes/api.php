<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ModuleController as StudentModuleController;
use App\Http\Controllers\Student\AssessmentController;
use App\Http\Controllers\Student\FeedbackController;
use App\Http\Controllers\Student\LessonCompletionController;
use App\Http\Controllers\Student\LessonController as StudentLessonController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Teacher\StudentMonitoringController;
use App\Http\Controllers\Teacher\FeedbackReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SystemLogController;

// Public auth routes — rate limited to blunt brute-force / registration spam.
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// Authenticated auth routes (any role)
Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
    Route::get('me',      [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
});

// Student routes
Route::prefix('student')
    ->middleware(['auth:sanctum', 'role:student'])
    ->group(function () {
        Route::get('dashboard',                  [StudentDashboardController::class, 'index']);
        Route::get('modules',                           [StudentModuleController::class, 'index']);
        Route::get('modules/{id}',                      [StudentModuleController::class, 'show']);
        Route::get('modules/{id}/quiz',                 [StudentModuleController::class, 'quiz']);
        Route::get('modules/{id}/completions',          [LessonCompletionController::class, 'forModule']);
        Route::post('competencies/{id}/complete',       [LessonCompletionController::class, 'complete']);
        Route::get('competencies/{id}/lesson-quiz',     [StudentLessonController::class, 'lessonWithQuiz']);
        Route::get('progress',                          [ProgressController::class, 'index']);
        Route::post('assessments/submit',               [AssessmentController::class, 'submit']);
        Route::get('feedback',                          [FeedbackController::class, 'index']);
        Route::get('feedback/{competencyId}',           [FeedbackController::class, 'show']);
    });

// Teacher routes
Route::prefix('teacher')
    ->middleware(['auth:sanctum', 'role:teacher'])
    ->group(function () {
        Route::get('students',              [StudentMonitoringController::class, 'index']);
        Route::get('students/{id}',         [StudentMonitoringController::class, 'show']);
        Route::get('feedback',              [FeedbackReviewController::class, 'index']);
        Route::post('feedback/{feedback}/approve', [FeedbackReviewController::class, 'approve']);
    });

// Admin routes
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {
        Route::get('stats',           [SystemLogController::class, 'stats']);
        Route::get('users',           [UserController::class, 'index']);
        Route::post('users',          [UserController::class, 'store']);
        Route::put('users/{id}',      [UserController::class, 'update']);
        Route::delete('users/{id}',   [UserController::class, 'destroy']);
        Route::get('logs',                    [SystemLogController::class, 'index']);
        Route::get('lessons-quizzes-mapping', [SystemLogController::class, 'lessonQuizMapping']);
    });
