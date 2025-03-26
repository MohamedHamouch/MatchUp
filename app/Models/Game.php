<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tournament;
use App\Models\User;

class Game extends Model
{
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    public function player1()
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    /**
     * Get the second player that owns the game.
     */
    public function player2()
    {
        return $this->belongsTo(User::class, 'player2_id');
    }
}
