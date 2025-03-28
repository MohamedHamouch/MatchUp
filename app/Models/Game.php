<?php

namespace App\Models;

use App\Models\User;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory;
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
