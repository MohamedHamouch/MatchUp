<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the identifier that will be stored in the JWT token.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return an array with custom claims to be added to the JWT token.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_user', 'user_id', 'tournament_id')
            ->withTimestamps();
    }

    public function gamesAsPlayer1()
    {
        return $this->hasMany(Game::class, 'player1_id');
    }

    public function gamesAsPlayer2()
    {
        return $this->hasMany(Game::class, 'player2_id');
    }

    public function getAllGames()
    {
        return Game::where('player1_id', $this->id)
            ->orWhere('player2_id', $this->id)
            ->get();
    }

    public function isRegisteredInTournament($tournamentId)
    {
        return $this->tournaments()->where('tournament_id', $tournamentId)->exists();
    }

    public function getWinCount()
    {
        $winsAsPlayer1 = $this->gamesAsPlayer1()
            ->whereNotNull('score_player1')
            ->whereNotNull('score_player2')
            ->whereRaw('score_player1 > score_player2')
            ->count();

        $winsAsPlayer2 = $this->gamesAsPlayer2()
            ->whereNotNull('score_player1')
            ->whereNotNull('score_player2')
            ->whereRaw('score_player2 > score_player1')
            ->count();

        return $winsAsPlayer1 + $winsAsPlayer2;
    }

    public function getLossCount()
    {
        $lossesAsPlayer1 = $this->gamesAsPlayer1()
            ->whereNotNull('score_player1')
            ->whereNotNull('score_player2')
            ->whereRaw('score_player1 < score_player2')
            ->count();

        $lossesAsPlayer2 = $this->gamesAsPlayer2()
            ->whereNotNull('score_player1')
            ->whereNotNull('score_player2')
            ->whereRaw('score_player2 < score_player1')
            ->count();

        return $lossesAsPlayer1 + $lossesAsPlayer2;
    }
}
