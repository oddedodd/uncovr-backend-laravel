<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistController;

Route::prefix('v1')->group(function () {
    // 🔑 Auth-ruter
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // 🎤 Artist CRUD (beskyttet med policy)
        Route::apiResource('artists', ArtistController::class);

        // 🎵 Artist dashboard – kun for brukere med rollen "artist"
        Route::middleware('role:artist')->group(function () {
            Route::get('/artist/dashboard', [ArtistController::class, 'dashboard']);
        });
    });
});