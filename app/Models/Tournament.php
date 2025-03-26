<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Game;
use App\Models\User;

class Tournament extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
    ];

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'tournament_user', 'tournament_id', 'user_id')
            ->withTimestamps();
    }
}
