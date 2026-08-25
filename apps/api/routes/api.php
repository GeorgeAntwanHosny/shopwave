<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PingController;


Route::get('/v1/ping', PingController::class);

use App\Http\Controllers\AuthController;

Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});
