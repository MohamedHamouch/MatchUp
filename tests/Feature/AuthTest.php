<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->validCredentials = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];
});

test('users can register', function () {
    $response = $this->postJson('/api/register', $this->validCredentials);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'name' => 'Test User',
    ]);
});

test('users can login', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'user' => ['id', 'name', 'email'],
            'token', // Updated to match the actual response field
        ]);
});

test('users cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'error' => 'Invalid credentials', // Updated to match the actual response structure
        ]);
});

test('authenticated users can logout', function () {
    $user = User::factory()->create();
    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Successfully logged out',
        ]);
});

test('authenticated users can get their profile', function () {
    $user = User::factory()->create();
    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'user' => ['id', 'name', 'email'],
        ])
        ->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
});