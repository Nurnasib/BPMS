<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\TaskController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    auth()->logout();
    auth()->login(User::first());
    return view('welcome');
});
Route::get('login', [LoginController::class, 'loginShow'])->name('login');
Route::get('register', [RegisterController::class, 'registerShow'])->name('register');
Route::get('projects', [ProjectController::class, 'projectShow'])->name('projects.view');
Route::get('projects/add', [ProjectController::class, 'projectAddShow'])->name('projects.add');
//Route::get('task/status/{task}', [TaskController::class, 'updateTaskStatus']);

