<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subscription> */
class SubscriptionFactory extends Factory
{
    /** @var list<string> */
    private const NAMES = [
        'Netflix', 'Spotify', 'GitHub Pro', 'DigitalOcean', 'Adobe Creative Cloud',
        'Microsoft 365', 'AWS', 'Notion', 'Figma', 'YouTube Premium',
        'Apple TV+', 'Amazon Prime', 'Cloudflare', 'JetBrains All Products',
    ];

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'name' => fake()->randomElement(self::NAMES),
            'description' => fake()->optional(0.4)->sentence(),
            'price' => fake()->randomFloat(2, 50, 500),
            'currency' => 'UAH',
            'billing_cycle' => fake()->randomElement(BillingCycle::cases()),
            'status' => SubscriptionStatus::Active,
            'started_at' => now()->subMonths(fake()->numberBetween(1, 24)),
            'next_billing_date' => now()->addDays(fake()->numberBetween(1, 30)),
            'cancelled_at' => null,
            'notify_days_before' => 3,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriptionStatus::Active,
            'cancelled_at' => null,
            'next_billing_date' => now()->addDays(fake()->numberBetween(5, 30)),
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriptionStatus::Paused,
            'cancelled_at' => null,
            'next_billing_date' => now()->addDays(fake()->numberBetween(5, 60)),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now()->subDays(fake()->numberBetween(1, 90)),
            'next_billing_date' => now()->subDays(fake()->numberBetween(1, 180)),
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(fake()->numberBetween(1, 3)),
        ]);
    }
}
