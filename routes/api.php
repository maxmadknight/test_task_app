<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskResourceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/tasks', [TaskResourceController::class, 'index']);
    Route::post('/tasks', [TaskResourceController::class, 'store']);
    Route::get('/tasks/{task}', [TaskResourceController::class, 'show']);
    Route::put('/tasks/{task}', [TaskResourceController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskResourceController::class, 'destroy']);
    Route::patch('/tasks/{task}/complete', [TaskResourceController::class, 'complete']);
});
