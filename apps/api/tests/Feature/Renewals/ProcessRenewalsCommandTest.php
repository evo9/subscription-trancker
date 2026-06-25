<?php

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
use App\Events\SubscriptionRenewed;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

describe('app:process-renewals', function (): void {
    it('creates a payment, advances the billing date, and fires SubscriptionRenewed', function (): void {
        Event::fake([SubscriptionRenewed::class]);

        $user = User::factory()->create();
        $sub = Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'price' => '10.00',
            'currency' => 'USD',
            'next_billing_date' => now()->toDateString(),
            'billing_cycle' => 'monthly',
        ]);

        $this->artisan('app:process-renewals')->assertSuccessful();

        expect(Payment::query()->where('subscription_id', $sub->getKey())->count())->toBe(1);

        $payment = Payment::query()->where('subscription_id', $sub->getKey())->first();
        expect($payment->amount)->toBe('10.00');
        expect($payment->currency)->toBe('USD');
        expect($payment->paid_at->toDateString())->toBe(now()->toDateString());

        $sub->refresh();
        expect($sub->next_billing_date->toDateString())->toBe(now()->addMonthNoOverflow()->toDateString());

        Event::assertDispatched(
            SubscriptionRenewed::class,
            fn (SubscriptionRenewed $e): bool => $e->subscription->getKey() === $sub->getKey(),
        );
    });

    it('does not duplicate a payment when re-run on the same day', function (): void {
        $user = User::factory()->create();
        $sub = Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'price' => '10.00',
            'currency' => 'USD',
            'next_billing_date' => now()->toDateString(),
            'billing_cycle' => 'monthly',
        ]);

        $this->artisan('app:process-renewals')->assertSuccessful();
        $this->artisan('app:process-renewals')->assertSuccessful();

        expect(Payment::query()->where('subscription_id', $sub->getKey())->count())->toBe(1);
    });

    it('skips paused subscriptions', function (): void {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Paused,
            'next_billing_date' => now()->toDateString(),
        ]);

        $this->artisan('app:process-renewals')->assertSuccessful();

        expect(Payment::query()->count())->toBe(0);
    });

    it('skips subscriptions not yet due', function (): void {
        $user = User::factory()->create();
        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDay()->toDateString(),
        ]);

        $this->artisan('app:process-renewals')->assertSuccessful();

        expect(Payment::query()->count())->toBe(0);
    });
});
