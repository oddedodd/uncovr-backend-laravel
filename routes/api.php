<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\PublicReleaseController;

Route::prefix('v1')->group(function () {
    // =========================
    // PUBLIC (ingen auth)
    // =========================
    Route::get('releases', [PublicReleaseController::class, 'index'])
        ->name('public.releases.index');

    Route::get('artists/{artist}/releases/public', [PublicReleaseController::class, 'byArtist'])
        ->name('public.releases.by-artist');

    // Eksplicit slug-route for å unngå kollisjon med releases/{release}
    Route::get('releases/slug/{slug}', [PublicReleaseController::class, 'showBySlug'])
        ->where('slug', '[A-Za-z0-9\-]+')
        ->name('public.releases.by-slug');

    // =========================
    // AUTH (krever token)
    // =========================
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Artist CRUD (policy styrer eierskap)
        Route::apiResource('artists', ArtistController::class);

        // Artist dashboard – kun for brukere med rollen "artist"
        Route::middleware('role:artist')->group(function () {
            Route::get('artist/dashboard', [ArtistController::class, 'dashboard']);
        });

        // Release (beskyttet) – nested (liste/opprett) + single (vis/oppdater/slett)
        Route::get('artists/{artist}/releases', [ReleaseController::class, 'index']);
        Route::post('artists/{artist}/releases', [ReleaseController::class, 'store']);

        Route::get('releases/{release}', [ReleaseController::class, 'show']);
        Route::patch('releases/{release}', [ReleaseController::class, 'update']);
        Route::delete('releases/{release}', [ReleaseController::class, 'destroy']);
    });
});