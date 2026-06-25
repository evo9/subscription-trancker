<?php

declare(strict_types=1);

use App\Enums\SubscriptionStatus;
use App\Jobs\SendRenewalReminderJob;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\RenewalReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

describe('SendRenewalReminderJob', function (): void {
    it('sends a RenewalReminder notification to the subscription owner', function (): void {
        Notification::fake();

        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(2)->toDateString(),
        ]);

        (new SendRenewalReminderJob($subscription))->handle();

        Notification::assertSentTo($user, RenewalReminder::class);
    });

    it('sends via mail and database channels', function (): void {
        Notification::fake();

        $user = User::factory()->create();
        $subscription = Subscription::factory()->for($user)->create([
            'status' => SubscriptionStatus::Active,
            'next_billing_date' => now()->addDays(2)->toDateString(),
        ]);

        (new SendRenewalReminderJob($subscription))->handle();

        Notification::assertSentTo(
            $user,
            RenewalReminder::class,
            fn (RenewalReminder $n, array $channels): bool => in_array('mail', $channels, true) && in_array('database', $channels, true),
        );
    });
});
