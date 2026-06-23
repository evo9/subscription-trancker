<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Auth', function (): void {
    it('registers a user and returns a token', function (): void {
        $response = $this->postJson('/api/register', [
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)->assertJsonStructure(['token']);
    });

    it('logs in with valid credentials', function (): void {
        User::factory()->create([
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'bob@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    });

    it('returns 401 for invalid credentials', function (): void {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong',
        ])->assertStatus(401);
    });

    it('returns the authenticated user', function (): void {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonFragment(['email' => $user->email]);
    });

    it('returns 401 for unauthenticated GET /api/user', function (): void {
        $this->getJson('/api/user')->assertStatus(401);
    });

    it('logs out and invalidates the token', function (): void {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertOk();

        // Reset the cached guard so the next request re-resolves from DB.
        auth()->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user')
            ->assertStatus(401);
    });
});
