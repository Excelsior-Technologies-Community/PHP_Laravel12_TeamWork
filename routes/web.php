<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInviteController;

/*
|--------------------------------------------------------------------------
| Public Invite Routes
|--------------------------------------------------------------------------
*/

Route::get('/invite/accept/{token}', [TeamInviteController::class, 'accept'])
    ->name('invite.accept');

Route::get('/invite/deny/{token}', [TeamInviteController::class, 'deny'])
    ->name('invite.deny');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |-----------------------------
    | TEAM ROUTES
    |-----------------------------
    */

    // View all teams + search
    Route::get('/teams', [TeamController::class, 'index'])
        ->name('teams.index');

    // Create team
    Route::post('/teams', [TeamController::class, 'store'])
        ->name('teams.store');

    // Invite member
    Route::post('/teams/{team}/invite', [TeamInviteController::class, 'invite'])
        ->name('teams.invite');

    // Remove member (NEW 🔥)
    Route::delete('/teams/{team}/remove/{user}', [TeamController::class, 'removeMember'])
        ->name('teams.remove');

    // Switch active team (NEW 🔥)
    Route::post('/teams/{team}/switch', [TeamController::class, 'switchTeam'])
        ->name('teams.switch');


    /*
    |-----------------------------
    | TEAM OWNER PROTECTED
    |-----------------------------
    */

    Route::get('/team/manage', function () {
        return "Only Team Owner Can Access";
    })->middleware('teamowner');


    /*
    |-----------------------------
    | PROFILE ROUTES
    |-----------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


/*
|--------------------------------------------------------------------------
| Default Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


require __DIR__ . '/auth.php';