<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Tournament;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => Game::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tournament_id' => 'required|exists:tournaments,id',
            'player1_id' => 'required|exists:users,id',
            'player2_id' => 'required|exists:users,id|different:player1_id',
            'score_player1' => 'nullable|integer|min:0',
            'score_player2' => 'nullable|integer|min:0',
        ]);

        $game = Game::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Game created successfully',
            'data' => $game,
        ]);
    }

    public function show($id)
    {
        $game = Game::findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $game]);
    }

    public function update(Request $request, $id)
    {
        $game = Game::findOrFail($id);

        $request->validate([
            'tournament_id' => 'sometimes|required|exists:tournaments,id',
            'player1_id' => 'sometimes|required|exists:users,id',
            'player2_id' => 'sometimes|required|exists:users,id|different:player1_id',
            'score_player1' => 'nullable|integer|min:0',
            'score_player2' => 'nullable|integer|min:0',
        ]);

        $game->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Game updated successfully',
            'data' => $game,
        ]);
    }

    public function destroy($id)
    {
        Game::findOrFail($id)->delete();

        return response()->json(['status' => 'success', 'message' => 'Game deleted successfully']);
    }
}