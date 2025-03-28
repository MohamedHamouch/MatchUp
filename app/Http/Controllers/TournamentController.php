<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TournamentController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::all();

        return response()->json([
            'status' => 'success',
            'data' => $tournaments
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tournament = Tournament::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tournament created successfully',
            'data' => $tournament
        ], 201);
    }

    public function show($id)
    {
        $tournament = Tournament::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $tournament
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tournament = Tournament::findOrFail($id);
        $tournament->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tournament updated successfully',
            'data' => $tournament,
        ]);
    }

    public function destroy($id)
    {
        $tournament = Tournament::findOrFail($id);
        $tournament->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tournament deleted successfully',
        ]);
    }
}
