<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UserObserver', function (): void {
    it('creates default categories when a user is created', function (): void {
        $user = User::factory()->create();

        expect($user->categories()->count())->toBe(5);

        $names = $user->categories()->orderBy('name')->pluck('name')->all();
        expect($names)->toBe(['Fitness', 'Hosting', 'Other', 'Software', 'Streaming']);
    });

    it('assigns a non-empty color to each default category', function (): void {
        $user = User::factory()->create();

        $user->categories()->each(function ($category): void {
            expect($category->color)->not->toBeEmpty();
        });
    });

    it('does not create duplicate categories when called twice', function (): void {
        $user = User::factory()->create();
        // observer fires only on User::created, not on update
        $user->touch();

        expect($user->categories()->count())->toBe(5);
    });
});
