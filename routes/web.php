<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// // ---------------------------
// // API routes (Sanctum based)
// // ---------------------------
// Route::prefix('api/v1')->group(function () {
//     Route::post('/auth/login', [AuthController::class, 'login']);

//     Route::middleware('auth:sanctum')->group(function () {
//         Route::get('/me', [AuthController::class, 'me']);
//         Route::post('/auth/logout', [AuthController::class, 'logout']);
//     });
// });