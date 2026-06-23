<?php

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('GET /api/subscriptions', function (): void {
    it('returns only the authenticated user subscriptions', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        Subscription::factory()->for($user)->count(2)->create();
        Subscription::factory()->for($other)->create();

        $this->getJson('/api/subscriptions')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('filters by status', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Subscription::factory()->for($user)->create(['status' => SubscriptionStatus::Active]);
        Subscription::factory()->for($user)->create(['status' => SubscriptionStatus::Paused]);

        $this->getJson('/api/subscriptions?status=active')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'active');
    });

    it('filters by category_id', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->for($user)->create();
        Subscription::factory()->for($user)->for($category)->create();
        Subscription::factory()->for($user)->create(['category_id' => null]);

        $this->getJson("/api/subscriptions?category_id={$category->getKey()}")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('filters by due_within', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Subscription::factory()->for($user)->create([
            'next_billing_date' => now()->addDays(3)->toDateString(),
        ]);
        Subscription::factory()->for($user)->create([
            'next_billing_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->getJson('/api/subscriptions?due_within=5')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('requires authentication', function (): void {
        $this->getJson('/api/subscriptions')->assertUnauthorized();
    });
});

describe('POST /api/subscriptions', function (): void {
    it('creates a subscription and returns 201', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'name' => 'Netflix',
            'price' => '15.99',
            'currency' => 'UAH',
            'billing_cycle' => 'monthly',
            'started_at' => '2026-01-01',
        ];

        $this->postJson('/api/subscriptions', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Netflix')
            ->assertJsonPath('data.status', 'active');

        expect(Subscription::query()->where('name', 'Netflix')->exists())->toBeTrue();
    });

    it('defaults next_billing_date to started_at when omitted', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/subscriptions', [
            'name' => 'Spotify',
            'price' => '9.99',
            'currency' => 'UAH',
            'billing_cycle' => 'monthly',
            'started_at' => '2026-01-15',
        ])->assertCreated()
            ->assertJsonPath('data.next_billing_date', '2026-01-15');
    });

    it('rejects a category belonging to another user', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $foreignCategory = Category::factory()->for($other)->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/subscriptions', [
            'name' => 'Test',
            'price' => '5.00',
            'currency' => 'UAH',
            'billing_cycle' => 'monthly',
            'started_at' => '2026-01-01',
            'category_id' => $foreignCategory->getKey(),
        ])->assertUnprocessable();
    });

    it('validates required fields', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/subscriptions', [])->assertUnprocessable();
    });
});

describe('GET /api/subscriptions/{id}', function (): void {
    it('returns the subscription with payments', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($user)->create();
        Payment::factory()->for($subscription)->create();

        $this->getJson("/api/subscriptions/{$subscription->getKey()}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'monthly_cost', 'yearly_cost', 'payments']]);
    });

    it('returns 403 when the subscription belongs to another user', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($other)->create();

        $this->getJson("/api/subscriptions/{$subscription->getKey()}")->assertForbidden();
    });
});

describe('PATCH /api/subscriptions/{id}', function (): void {
    it('updates the subscription', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($user)->create(['name' => 'Old Name']);

        $this->patchJson("/api/subscriptions/{$subscription->getKey()}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    });

    it('returns 403 when the subscription belongs to another user', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($other)->create();

        $this->patchJson("/api/subscriptions/{$subscription->getKey()}", ['name' => 'Hack'])
            ->assertForbidden();
    });
});

describe('DELETE /api/subscriptions/{id}', function (): void {
    it('soft deletes and returns 204', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($user)->create();

        $this->deleteJson("/api/subscriptions/{$subscription->getKey()}")->assertNoContent();

        expect(Subscription::query()->find($subscription->getKey()))->toBeNull();
        expect(Subscription::withTrashed()->find($subscription->getKey()))->not->toBeNull();
    });

    it('returns 403 when the subscription belongs to another user', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($other)->create();

        $this->deleteJson("/api/subscriptions/{$subscription->getKey()}")->assertForbidden();
    });
});

describe('POST /api/subscriptions/{id}/pause', function (): void {
    it('sets status to paused', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($user)->create(['status' => SubscriptionStatus::Active]);

        $this->postJson("/api/subscriptions/{$subscription->getKey()}/pause")
            ->assertOk()
            ->assertJsonPath('data.status', 'paused');
    });

    it('returns 403 when the subscription belongs to another user', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($other)->create();

        $this->postJson("/api/subscriptions/{$subscription->getKey()}/pause")->assertForbidden();
    });
});

describe('POST /api/subscriptions/{id}/resume', function (): void {
    it('sets status back to active', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($user)->create(['status' => SubscriptionStatus::Paused]);

        $this->postJson("/api/subscriptions/{$subscription->getKey()}/resume")
            ->assertOk()
            ->assertJsonPath('data.status', 'active');
    });

    it('returns 403 when the subscription belongs to another user', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($other)->create();

        $this->postJson("/api/subscriptions/{$subscription->getKey()}/resume")->assertForbidden();
    });
});

describe('GET /api/subscriptions/{id}/payments', function (): void {
    it('returns the correct payment count', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($user)->create();
        Payment::factory()->for($subscription)->count(3)->create();

        $this->getJson("/api/subscriptions/{$subscription->getKey()}/payments")
            ->assertOk()
            ->assertJsonCount(3, 'data');
    });

    it('returns 403 when the subscription belongs to another user', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        $subscription = Subscription::factory()->for($other)->create();

        $this->getJson("/api/subscriptions/{$subscription->getKey()}/payments")->assertForbidden();
    });
});
