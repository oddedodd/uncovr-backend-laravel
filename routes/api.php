<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicReleaseController; // ✅ riktig namespace
use App\Http\Controllers\PublicPageController;    // ✅ riktig namespace

Route::prefix('v1')->group(function () {
    // =========================
    // PUBLIC (ingen auth)
    // =========================

    // Alle publiserte releases (støtter ev. ?type=single|ep|album & ?artist_id=ID)
    Route::get('releases', [PublicReleaseController::class, 'index'])
        ->name('public.releases.index');

    // Publiserte releases for en bestemt artist
    Route::get('artists/{artist}/releases/public', [PublicReleaseController::class, 'byArtist'])
        ->name('public.releases.by-artist');

    // Én publisert release via slug
    Route::get('releases/slug/{slug}', [PublicReleaseController::class, 'showBySlug'])
        ->where('slug', '[A-Za-z0-9\-]+')
        ->name('public.releases.by-slug');

    // Publiserte sider for en release via slug
    Route::get('releases/slug/{slug}/pages', [PublicPageController::class, 'byReleaseSlug'])
        ->where('slug', '[A-Za-z0-9\-]+')
        ->name('public.pages.by-release-slug');

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

        // Release (beskyttet)
        Route::get('artists/{artist}/releases', [ReleaseController::class, 'index']);
        Route::post('artists/{artist}/releases', [ReleaseController::class, 'store']);
        Route::get('releases/{release}', [ReleaseController::class, 'show']);
        Route::patch('releases/{release}', [ReleaseController::class, 'update']);
        Route::delete('releases/{release}', [ReleaseController::class, 'destroy']);

        // Page (beskyttet)
        Route::get('releases/{release}/pages', [PageController::class, 'index']);
        Route::post('releases/{release}/pages', [PageController::class, 'store']);
        Route::get('pages/{page}', [PageController::class, 'show']);
        Route::patch('pages/{page}', [PageController::class, 'update']);
        Route::delete('pages/{page}', [PageController::class, 'destroy']);
    });
});