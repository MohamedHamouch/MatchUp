<?php

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
    
    $this->token = auth('api')->login($this->user);
    
    $this->tournamentData = [
        'name' => 'Chess Championship',
        'description' => 'Annual chess tournament for all skill levels',
        'start_date' => now()->addDays(10)->format('Y-m-d'),
        'end_date' => now()->addDays(15)->format('Y-m-d'),
    ];
});

test('authenticated users can create tournaments', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/tournaments', $this->tournamentData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['id', 'name', 'description', 'start_date', 'end_date']
        ]);
        
    $this->assertDatabaseHas('tournaments', [
        'name' => 'Chess Championship',
        'description' => 'Annual chess tournament for all skill levels',
    ]);
});

test('authenticated users can list all tournaments', function () {
    Tournament::factory()->count(3)->create();
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/tournaments');
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => [
                'data' => [
                    '*' => ['id', 'name', 'description', 'start_date', 'end_date']
                ]
            ]
        ]);
        
    $this->assertEquals(3, count($response->json('data.data')));
});

test('authenticated users can view a specific tournament', function () {
    $tournament = Tournament::factory()->create([
        'name' => 'Basketball Tournament',
        'description' => 'Basketball tournament for teams of 5'
    ]);
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/tournaments/{$tournament->id}");
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'data' => ['id', 'name', 'description', 'start_date', 'end_date']
        ])
        ->assertJson([
            'data' => [
                'name' => 'Basketball Tournament',
                'description' => 'Basketball tournament for teams of 5'
            ]
        ]);
});

test('authenticated users can update a tournament', function () {
    $tournament = Tournament::factory()->create();
    
    $updatedData = [
        'name' => 'Updated Tournament Name',
        'description' => 'This description has been updated'
    ];
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->putJson("/api/tournaments/{$tournament->id}", $updatedData);
        
    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['id', 'name', 'description', 'start_date', 'end_date']
        ])
        ->assertJson([
            'data' => [
                'name' => 'Updated Tournament Name',
                'description' => 'This description has been updated'
            ]
        ]);
        
    $this->assertDatabaseHas('tournaments', [
        'id' => $tournament->id,
        'name' => 'Updated Tournament Name',
        'description' => 'This description has been updated'
    ]);
});

test('authenticated users can delete a tournament', function () {
    $tournament = Tournament::factory()->create();
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->deleteJson("/api/tournaments/{$tournament->id}");
        
    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'message' => 'Tournament deleted successfully'
        ]);
        
    $this->assertDatabaseMissing('tournaments', [
        'id' => $tournament->id
    ]);
});

test('validation errors are returned when creating tournaments with invalid data', function () {
    $invalidData = [
        'name' => '',  // name is required
        'start_date' => now()->format('Y-m-d'),
        'end_date' => now()->subDays(5)->format('Y-m-d'), // end_date before start_date
    ];
    
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/tournaments', $invalidData);
        
    $response->assertStatus(422)
        ->assertJsonStructure(['errors']);
        
    $this->assertArrayHasKey('name', $response->json('errors'));
    $this->assertArrayHasKey('end_date', $response->json('errors'));
});