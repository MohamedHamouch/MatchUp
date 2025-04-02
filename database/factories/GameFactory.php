<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'tournament_id' => Tournament::factory(),
            'player1_id' => User::factory(),
            'player2_id' => User::factory(),
            'score_player1' => $this->faker->optional(0.7)->numberBetween(0, 10),
            'score_player2' => $this->faker->optional(0.7)->numberBetween(0, 10),
        ];
    }

    
    public function completed()
    {
        return $this->state(function ($attributes) {
            return [
                'score_player1' => $this->faker->numberBetween(0, 10),
                'score_player2' => $this->faker->numberBetween(0, 10),
            ];
        });
    }
}
