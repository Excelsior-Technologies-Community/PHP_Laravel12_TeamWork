<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInviteController;


/*
|--------------------------------------------------------------------------
| Invite Routes (Public)
|--------------------------------------------------------------------------
*/

Route::get('/invite/accept/{token}', [TeamInviteController::class, 'accept']);
Route::get('/invite/deny/{token}', [TeamInviteController::class, 'deny']);



Route::middleware(['auth'])->group(function () {

    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');

    Route::post('/teams/{team}/invite', [TeamInviteController::class, 'invite'])
        ->name('teams.invite');

    // Team Owner Protected Route
    Route::get('/team/manage', function () {
        return "Only Team Owner Can Access";
    })->middleware('teamowner');
});

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

require __DIR__ . '/auth.php';
