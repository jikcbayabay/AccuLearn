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
use App\Http\Controllers\Admin\ModuleManagementController;
use App\Http\Controllers\Admin\SystemLogController;

Route::prefix('auth')->group(function () {
    Route::post('login',  [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me',      [AuthController::class, 'me']);
});

Route::prefix('student')
    ->middleware(['auth:sanctum', 'role:student'])
    ->group(function () {
        Route::get('dashboard',                          [StudentDashboardController::class, 'index']);
        Route::get('modules',                            [StudentModuleController::class, 'index']);
        Route::get('modules/{module}',                   [StudentModuleController::class, 'show']);
        Route::get('assessments/{assessment}',           [AssessmentController::class, 'show']);
        Route::post('assessments/{assessment}/submit',   [AssessmentController::class, 'submit']);
        Route::get('feedback/{competency}',              [FeedbackController::class, 'show']);
        Route::get('progress',                           [ProgressController::class, 'index']);
    });

Route::prefix('teacher')
    ->middleware(['auth:sanctum', 'role:teacher'])
    ->group(function () {
        Route::get('overview',                                  [StudentMonitoringController::class, 'overview']);
        Route::get('students',                                  [StudentMonitoringController::class, 'index']);
        Route::get('students/{user}/mastery',                   [StudentMonitoringController::class, 'mastery']);
        Route::get('feedback',                                  [FeedbackReviewController::class, 'index']);
        Route::post('feedback/{feedback}/approve',              [FeedbackReviewController::class, 'approve']);
    });

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {
        Route::get('overview',              [UserController::class, 'overview']);
        Route::get('users',                 [UserController::class, 'index']);
        Route::post('users',                [UserController::class, 'store']);
        Route::put('users/{user}',          [UserController::class, 'update']);
        Route::delete('users/{user}',       [UserController::class, 'destroy']);

        Route::get('modules',               [ModuleManagementController::class, 'index']);
        Route::post('modules',              [ModuleManagementController::class, 'store']);
        Route::put('modules/{module}',      [ModuleManagementController::class, 'update']);
        Route::delete('modules/{module}',   [ModuleManagementController::class, 'destroy']);

        Route::get('logs',                  [SystemLogController::class, 'index']);
    });
