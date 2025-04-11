<?php

use App\Models\Game;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = auth('api')->login($this->user);
    
    $this->tournament = Tournament::factory()->create();
    
    // Create players
    $this->player1 = User::factory()->create();
    $this->player2 = User::factory()->create();
    
    // Register players in tournament
    $this->tournament->players()->attach($this->player1->id);
    $this->tournament->players()->attach($this->player2->id);
    
    $this->gameData = [
        'tournament_id' => $this->tournament->id,
        'player1_id' => $this->player1->id,
        'player2_id' => $this->player2->id,
    ];
});

test('authenticated users can create games', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/games', $this->gameData);
        
    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['id', 'tournament_id', 'player1_id', 'player2_id']
        ]);
        
    $this->assertDatabaseHas('games', [
        'tournament_id' => $this->tournament->id,
        'player1_id' => $this->player1->id,
        'player2_id' => $this->player2->id,
    ]);
});

test('authenticated users can list all games', function () {
    Game::factory()->count(3)->create([
        'tournament_id' => $this->tournament->id
    ]);
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/games');
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'data' => [
                    '*' => ['id', 'tournament_id', 'player1_id', 'player2_id']
                ]
            ]
        ]);
        
    $this->assertEquals(3, count($response->json('data.data')));
});

test('authenticated users can filter games by tournament', function () {
    $tournament1 = Tournament::factory()->create();
    $tournament2 = Tournament::factory()->create();
    
    // Create 2 games for tournament1
    Game::factory()->count(2)->create([
        'tournament_id' => $tournament1->id
    ]);
    
    // Create 3 games for tournament2
    Game::factory()->count(3)->create([
        'tournament_id' => $tournament2->id
    ]);
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/games?tournament_id={$tournament1->id}");
        
    $response->assertStatus(200);
    $this->assertEquals(2, count($response->json('data.data')));
});

test('authenticated users can view a specific game', function () {
    $game = Game::factory()->create($this->gameData);
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/games/{$game->id}");
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => ['id', 'tournament_id', 'player1_id', 'player2_id']
        ])
        ->assertJson([
            'data' => [
                'tournament_id' => $this->tournament->id,
                'player1_id' => $this->player1->id,
                'player2_id' => $this->player2->id,
            ]
        ]);
});

test('authenticated users can update a game', function () {
    $game = Game::factory()->create($this->gameData);
    
    // Create new player for the update
    $newPlayer = User::factory()->create();
    $this->tournament->players()->attach($newPlayer->id);
    
    $updatedData = [
        'player2_id' => $newPlayer->id,
    ];
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->putJson("/api/games/{$game->id}", $updatedData);
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['id', 'tournament_id', 'player1_id', 'player2_id']
        ])
        ->assertJson([
            'data' => [
                'player2_id' => $newPlayer->id
            ]
        ]);
        
    $this->assertDatabaseHas('games', [
        'id' => $game->id,
        'player2_id' => $newPlayer->id
    ]);
});

test('authenticated users can add scores to a game', function () {
    $game = Game::factory()->create($this->gameData);
    
    $scoreData = [
        'score_player1' => 5,
        'score_player2' => 3
    ];
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/games/{$game->id}/scores", $scoreData);
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['id', 'tournament_id', 'player1_id', 'player2_id', 'score_player1', 'score_player2']
        ])
        ->assertJson([
            'data' => [
                'score_player1' => 5,
                'score_player2' => 3
            ]
        ]);
        
    $this->assertDatabaseHas('games', [
        'id' => $game->id,
        'score_player1' => 5,
        'score_player2' => 3
    ]);
});

test('authenticated users can update scores of a game', function () {
    $game = Game::factory()->create(array_merge($this->gameData, [
        'score_player1' => 2,
        'score_player2' => 2
    ]));
    
    $updatedScoreData = [
        'score_player1' => 3,
        'score_player2' => 4
    ];
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->putJson("/api/games/{$game->id}/scores", $updatedScoreData);
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['id', 'tournament_id', 'player1_id', 'player2_id', 'score_player1', 'score_player2']
        ])
        ->assertJson([
            'data' => [
                'score_player1' => 3,
                'score_player2' => 4
            ]
        ]);
        
    $this->assertDatabaseHas('games', [
        'id' => $game->id,
        'score_player1' => 3,
        'score_player2' => 4
    ]);
});

test('authenticated users can delete a game', function () {
    $game = Game::factory()->create($this->gameData);
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->deleteJson("/api/games/{$game->id}");
        
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Game deleted successfully'
        ]);
        
    $this->assertDatabaseMissing('games', [
        'id' => $game->id
    ]);
});

test('validation errors are returned when creating games with invalid data', function () {
    $invalidData = [
        'tournament_id' => $this->tournament->id,
        'player1_id' => $this->player1->id,
        'player2_id' => $this->player1->id, // Same as player1, should be different
    ];
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/games', $invalidData);
        
    $response->assertStatus(422)
        ->assertJsonStructure(['errors']);
        
    $this->assertArrayHasKey('player2_id', $response->json('errors'));
});