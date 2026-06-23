<?php

declare(strict_types=1);

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('Subscription model', function (): void {
    describe('monthlyCost accessor', function (): void {
        it('returns price as-is for monthly billing', function (): void {
            $sub = Subscription::factory()->make([
                'price' => '10.00',
                'billing_cycle' => BillingCycle::Monthly,
            ]);
            expect($sub->monthly_cost)->toBe(10.0);
        });

        it('normalises yearly price to monthly', function (): void {
            $sub = Subscription::factory()->make([
                'price' => '120.00',
                'billing_cycle' => BillingCycle::Yearly,
            ]);
            expect($sub->monthly_cost)->toBe(10.0);
        });

        it('normalises weekly price to monthly', function (): void {
            $sub = Subscription::factory()->make([
                'price' => '10.00',
                'billing_cycle' => BillingCycle::Weekly,
            ]);
            // 10 * 52 / 12 = 43.33
            expect($sub->monthly_cost)->toBe(round(10.0 * 52 / 12, 2));
        });

        it('normalises quarterly price to monthly', function (): void {
            $sub = Subscription::factory()->make([
                'price' => '30.00',
                'billing_cycle' => BillingCycle::Quarterly,
            ]);
            // 30 * 4 / 12 = 10.0
            expect($sub->monthly_cost)->toBe(10.0);
        });
    });

    describe('yearlyCost accessor', function (): void {
        it('computes yearly cost for monthly billing', function (): void {
            $sub = Subscription::factory()->make([
                'price' => '10.00',
                'billing_cycle' => BillingCycle::Monthly,
            ]);
            expect($sub->yearly_cost)->toBe(120.0);
        });

        it('returns price as-is for yearly billing', function (): void {
            $sub = Subscription::factory()->make([
                'price' => '120.00',
                'billing_cycle' => BillingCycle::Yearly,
            ]);
            expect($sub->yearly_cost)->toBe(120.0);
        });
    });

    describe('scopeForUser', function (): void {
        it('returns only the given user subscriptions', function (): void {
            $user = User::factory()->create();
            $other = User::factory()->create();
            Subscription::factory()->for($user)->create();
            Subscription::factory()->for($other)->create();

            $results = Subscription::query()->forUser($user)->get();

            expect($results)->toHaveCount(1);
            expect($results->first()->user_id)->toBe($user->getKey());
        });
    });

    describe('scopeActive', function (): void {
        it('returns only active subscriptions', function (): void {
            $user = User::factory()->create();
            Subscription::factory()->for($user)->create(['status' => SubscriptionStatus::Active]);
            Subscription::factory()->for($user)->create(['status' => SubscriptionStatus::Paused]);

            $results = Subscription::query()->active()->get();

            expect($results)->toHaveCount(1);
            expect($results->first()->status)->toBe(SubscriptionStatus::Active);
        });
    });

    describe('scopeDueWithin', function (): void {
        it('returns subscriptions whose next_billing_date falls within the given days', function (): void {
            $user = User::factory()->create();
            Subscription::factory()->for($user)->create([
                'next_billing_date' => now()->addDays(3)->toDateString(),
            ]);
            Subscription::factory()->for($user)->create([
                'next_billing_date' => now()->addDays(10)->toDateString(),
            ]);

            $results = Subscription::query()->dueWithin(5)->get();

            expect($results)->toHaveCount(1);
        });
    });
});
