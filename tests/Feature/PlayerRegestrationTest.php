<?php

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = auth('api')->login($this->user);
    $this->tournament = Tournament::factory()->create();
});

test('players can register for tournaments', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/tournaments/{$this->tournament->id}/players");
        
    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['id', 'name', 'email']
        ]);
        
    $this->assertDatabaseHas('tournament_user', [
        'tournament_id' => $this->tournament->id,
        'user_id' => $this->user->id
    ]);
});

test('players can view all participants in a tournament', function () {
    // Register 3 players for the tournament
    $players = User::factory()->count(3)->create();
    
    foreach ($players as $player) {
        $this->tournament->players()->attach($player->id);
    }
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/tournaments/{$this->tournament->id}/players");
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'email']
                ]
            ]
        ]);
        
    $this->assertEquals(3, count($response->json('data.data')));
});

test('players can unregister from tournaments', function () {
    // First register the user
    $this->tournament->players()->attach($this->user->id);
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->deleteJson("/api/tournaments/{$this->tournament->id}/players/{$this->user->id}");
        
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Player removed from tournament successfully'
        ]);
        
    $this->assertDatabaseMissing('tournament_user', [
        'tournament_id' => $this->tournament->id,
        'user_id' => $this->user->id
    ]);
});

test('players cannot register twice for the same tournament', function () {
    // First registration
    $this->tournament->players()->attach($this->user->id);
    
    // Try to register again
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson("/api/tournaments/{$this->tournament->id}/players");
        
    $response->assertStatus(422)
        ->assertJson([
            'status' => 'error',
            'message' => 'Player is already registered in this tournament'
        ]);
});