<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GameController;

//auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt.auth');
Route::get('/user', [AuthController::class, 'user'])->middleware('jwt.auth');

// Route::get('/test', function () {
//     return response()->json(['Stauts' => 'You are allowed']);
// });

//tournament
Route::middleware('jwt.auth')->group(function () {
    Route::post('/tournaments', [TournamentController::class, 'store']);
    Route::get('/tournaments', [TournamentController::class, 'index']);
    Route::get('/tournaments/{id}', [TournamentController::class, 'show']);
    Route::put('/tournaments/{id}', [TournamentController::class, 'update']);
    Route::delete('/tournaments/{id}', [TournamentController::class, 'destroy']);

    //Player
    Route::post('/tournaments/{tournament_id}/players', [UserController::class, 'store']);
    Route::get('/tournaments/{tournament_id}/players', [UserController::class, 'index']);
    Route::delete('/tournaments/{tournament_id}/players/{player_id}', [UserController::class, 'destroy']);

    // Game 
    Route::post('/games', [GameController::class, 'store']);
    Route::get('/games', [GameController::class, 'index']);
    Route::get('/games/{id}', [GameController::class, 'show']);
    Route::put('/games/{id}', [GameController::class, 'update']);
    Route::delete('/games/{id}', [GameController::class, 'destroy']);
    Route::post('/games/{id}/scores', [GameController::class, 'addScore']);
    Route::put('/games/{id}/scores', [GameController::class, 'updateScore']);
});