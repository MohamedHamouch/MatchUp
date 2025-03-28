<?php

namespace App\Models;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Tournament extends Model
{
    use HasFactory;

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
