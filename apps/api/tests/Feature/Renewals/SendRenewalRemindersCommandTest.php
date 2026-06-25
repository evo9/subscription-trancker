<?php

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
use App\Jobs\SendRenewalReminderJob;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

describe('app:send-renewal-reminders', function (): void {
    it('dispatches a job for each subscription in the reminder window', function (): void {
        Queue::fake();

        $user = User::factory()->create();

        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(2)->toDateString(),
            'notify_days_before' => 3,
        ]);

        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(3)->toDateString(),
            'notify_days_before' => 3,
        ]);

        $this->artisan('app:send-renewal-reminders')->assertSuccessful();

        Queue::assertPushed(SendRenewalReminderJob::class, 2);
    });

    it('does not dispatch jobs for subscriptions outside the reminder window', function (): void {
        Queue::fake();

        $user = User::factory()->create();

        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(10)->toDateString(),
            'notify_days_before' => 3,
        ]);

        $this->artisan('app:send-renewal-reminders')->assertSuccessful();

        Queue::assertNothingPushed();
    });

    it('does not dispatch jobs for paused subscriptions', function (): void {
        Queue::fake();

        $user = User::factory()->create();

        Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Paused,
            'next_billing_date' => now()->addDays(2)->toDateString(),
            'notify_days_before' => 3,
        ]);

        $this->artisan('app:send-renewal-reminders')->assertSuccessful();

        Queue::assertNothingPushed();
    });
});
