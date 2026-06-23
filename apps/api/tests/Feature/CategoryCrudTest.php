<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('GET /api/categories', function (): void {
    it('returns only the authenticated user categories', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        Category::factory()->for($user)->count(3)->create();
        $otherCategory = Category::factory()->for($other)->create();

        $expected = Category::query()->where('user_id', $user->getKey())->count();

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount($expected, 'data')
            ->assertJsonMissing(['id' => $otherCategory->getKey()]);
    });

    it('requires authentication', function (): void {
        $this->getJson('/api/categories')->assertUnauthorized();
    });
});

describe('POST /api/categories', function (): void {
    it('creates a category and returns 201', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/categories', ['name' => 'Streaming', 'color' => '#E50914'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Streaming')
            ->assertJsonPath('data.color', '#E50914');

        expect(Category::query()->where('name', 'Streaming')->exists())->toBeTrue();
    });

    it('defaults color to #6366f1 when omitted', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/categories', ['name' => 'Music'])
            ->assertCreated()
            ->assertJsonPath('data.color', '#6366f1');
    });

    it('validates required name', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/categories', [])->assertUnprocessable();
    });

    it('rejects an invalid color format', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/categories', ['name' => 'Test', 'color' => 'red'])
            ->assertUnprocessable();
    });
});

describe('PATCH /api/categories/{id}', function (): void {
    it('updates the category', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->for($user)->create(['name' => 'Old Name']);

        $this->patchJson("/api/categories/{$category->getKey()}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    });

    it('returns 403 when the category belongs to another user', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->for($other)->create();

        $this->patchJson("/api/categories/{$category->getKey()}", ['name' => 'Hack'])
            ->assertForbidden();
    });
});

describe('DELETE /api/categories/{id}', function (): void {
    it('deletes the category and returns 204', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->for($user)->create();

        $this->deleteJson("/api/categories/{$category->getKey()}")->assertNoContent();

        expect(Category::query()->find($category->getKey()))->toBeNull();
    });

    it('sets category_id to null on related subscriptions', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->for($user)->create();
        $subscription = Subscription::factory()->for($user)->for($category)->create();

        $this->deleteJson("/api/categories/{$category->getKey()}")->assertNoContent();

        expect($subscription->fresh()?->category_id)->toBeNull();
    });

    it('returns 403 when the category belongs to another user', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->for($other)->create();

        $this->deleteJson("/api/categories/{$category->getKey()}")->assertForbidden();
    });
});
