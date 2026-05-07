<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ModuleController as StudentModuleController;
use App\Http\Controllers\Student\AssessmentController;
use App\Http\Controllers\Student\FeedbackController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Teacher\StudentMonitoringController;
use App\Http\Controllers\Teacher\FeedbackReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SystemLogController;

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
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
        Route::get('modules',                    [StudentModuleController::class, 'index']);
        Route::get('modules/{id}',               [StudentModuleController::class, 'show']);
        Route::get('progress',                   [ProgressController::class, 'index']);
        Route::post('assessments/submit',        [AssessmentController::class, 'submit']);
        Route::get('feedback/{competencyId}',    [FeedbackController::class, 'show']);
    });

// Teacher routes
Route::prefix('teacher')
    ->middleware(['auth:sanctum', 'role:teacher'])
    ->group(function () {
        Route::get('students',         [StudentMonitoringController::class, 'index']);
        Route::get('students/{id}',    [StudentMonitoringController::class, 'show']);
        Route::get('feedback',         [FeedbackReviewController::class, 'index']);
    });

// Admin routes
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {
        Route::get('users',           [UserController::class, 'index']);
        Route::post('users',          [UserController::class, 'store']);
        Route::put('users/{id}',      [UserController::class, 'update']);
        Route::delete('users/{id}',   [UserController::class, 'destroy']);
        Route::get('logs',            [SystemLogController::class, 'index']);
    });
