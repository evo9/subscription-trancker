<?php

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('GET /api/stats/summary', function (): void {
    it('returns monthly and yearly totals for active subscriptions', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Subscription::factory()->for($user)->create([
            'price' => '10.00',
            'billing_cycle' => 'monthly',
            'status' => SubscriptionStatus::Active,
        ]);

        Subscription::factory()->for($user)->create([
            'price' => '120.00',
            'billing_cycle' => 'yearly',
            'status' => SubscriptionStatus::Active,
        ]);

        $this->getJson('/api/stats/summary')
            ->assertOk()
            ->assertJsonPath('data.monthly_total', 20)
            ->assertJsonPath('data.yearly_total', 240);
    });

    it('excludes paused subscriptions from totals', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Subscription::factory()->for($user)->create([
            'price' => '10.00',
            'billing_cycle' => 'monthly',
            'status' => SubscriptionStatus::Active,
        ]);

        Subscription::factory()->for($user)->create([
            'price' => '50.00',
            'billing_cycle' => 'monthly',
            'status' => SubscriptionStatus::Paused,
        ]);

        $this->getJson('/api/stats/summary')
            ->assertOk()
            ->assertJsonPath('data.monthly_total', 10)
            ->assertJsonPath('data.yearly_total', 120);
    });

    it('groups totals by category', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $category = Category::factory()->for($user)->create(['name' => 'Streaming', 'color' => '#E50914']);

        Subscription::factory()->for($user)->for($category)->create([
            'price' => '10.00',
            'billing_cycle' => 'monthly',
            'status' => SubscriptionStatus::Active,
        ]);

        Subscription::factory()->for($user)->create([
            'price' => '120.00',
            'billing_cycle' => 'yearly',
            'status' => SubscriptionStatus::Active,
            'category_id' => null,
        ]);

        $response = $this->getJson('/api/stats/summary')->assertOk();

        $byCategory = $response->json('data.by_category');
        expect($byCategory)->toHaveCount(2);

        /** @var array<string, mixed> $categorized */
        $categorized = collect($byCategory)->firstWhere('category_id', $category->getKey()); // @phpstan-ignore-line -- collect() on mixed[] loses generic types
        expect($categorized)->not->toBeNull();
        expect($categorized['monthly_total'])->toBe(10);

        /** @var array<string, mixed> $uncategorized */
        $uncategorized = collect($byCategory)->firstWhere('category_id', null); // @phpstan-ignore-line -- collect() on mixed[] loses generic types
        expect($uncategorized)->not->toBeNull();
        expect($uncategorized['monthly_total'])->toBe(10);
    });

    it('does not include another user subscriptions in totals', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        Subscription::factory()->for($user)->create([
            'price' => '10.00',
            'billing_cycle' => 'monthly',
            'status' => SubscriptionStatus::Active,
        ]);

        Subscription::factory()->for($other)->create([
            'price' => '999.00',
            'billing_cycle' => 'monthly',
            'status' => SubscriptionStatus::Active,
        ]);

        $this->getJson('/api/stats/summary')
            ->assertOk()
            ->assertJsonPath('data.monthly_total', 10);
    });

    it('requires authentication', function (): void {
        $this->getJson('/api/stats/summary')->assertUnauthorized();
    });
});

describe('GET /api/stats/upcoming', function (): void {
    it('returns active subscriptions due within 30 days ordered by date', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $soon = Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(5)->toDateString(),
        ]);

        $later = Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(3)->toDateString(),
        ]);

        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(60)->toDateString(),
        ]);

        $this->getJson('/api/stats/upcoming')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $later->getKey())
            ->assertJsonPath('data.1.id', $soon->getKey());
    });

    it('excludes paused subscriptions', function (): void {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(5)->toDateString(),
        ]);

        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Paused,
            'next_billing_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->getJson('/api/stats/upcoming')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('does not include another user subscriptions', function (): void {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($user);

        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(5)->toDateString(),
        ]);

        Subscription::factory()->for($other)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->getJson('/api/stats/upcoming')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    });

    it('requires authentication', function (): void {
        $this->getJson('/api/stats/upcoming')->assertUnauthorized();
    });
});
