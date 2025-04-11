<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index($tournament_id)
    {
        $tournament = Tournament::findOrFail($tournament_id);

        return response()->json(['status' => 'success', 'data' => $tournament->players]);
    }

    public function store(Request $request, $tournament_id)
    {
        $tournament = Tournament::findOrFail($tournament_id);
        $player_id = $request->input('player_id');

        if ($tournament->players()->where('user_id', $player_id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Player is already registered in this tournament',
            ], 422);
        }

        $tournament->players()->attach($player_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Player added to tournament successfully',
        ]);
    }

    public function destroy($tournament_id, $player_id)
    {
        $tournament = Tournament::findOrFail($tournament_id);

        if (!$tournament->players()->where('user_id', $player_id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Player is not registered in this tournament',
            ], 404);
        }

        $tournament->players()->detach($player_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Player removed from tournament successfully',
        ]);
    }
}