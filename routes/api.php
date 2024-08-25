<?php

use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\SubtaskController;
use App\Http\Controllers\Project\TaskController;
use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;


Route::post('login', [LoginController::class, 'login']);



Route::middleware('auth:api')->group(function () {
    Route::middleware(['role:admin,team_leader'])->group(function () {

        Route::post('register', [RegisterController::class, 'register']);

        Route::resource('projects', ProjectController::class);
        Route::apiResource('tasks', TaskController::class);
        Route::apiResource('subtasks', SubtaskController::class);
        Route::get('projects/{project}/tasks', [TaskController::class, 'getTasksByProject']);

        Route::get('/reports/projects', [ReportController::class, 'getProjectReport']);
        Route::get('/reports/projects/export', [ReportController::class, 'exportProjectsReport']);

        Route::get('subtask/status/{subtask}', [SubtaskController::class, 'updateSubtaskStatus']);
    });




});
