<?php

namespace Database\Seeders;

use App\Models\Game;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Tournament;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // Create 20 users
    $users = User::factory()->count(20)->create();
    
    // Create 5 tournaments with players and games
    Tournament::factory()
        ->count(5)
        ->create()
        ->each(function ($tournament) use ($users) {
            // Attach 8 random users as players
            $players = $users->random(8);
            $tournament->players()->attach($players->pluck('id'));
            
            // Create some games between these players
            $playerIds = $players->pluck('id')->toArray();
            for ($i = 0; $i < 10; $i++) {
                shuffle($playerIds);
                Game::factory()->create([
                    'tournament_id' => $tournament->id,
                    'player1_id' => $playerIds[0],
                    'player2_id' => $playerIds[1],
                    // Sometimes create completed games, sometimes not
                    'score_player1' => rand(0, 1) ? rand(0, 10) : null,
                    'score_player2' => rand(0, 1) ? rand(0, 10) : null,
                ]);
            }
        });
}
}
