<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;

final class PauseSubscription
{
    public function handle(Subscription $subscription): Subscription
    {
        $subscription->update(['status' => SubscriptionStatus::Paused]);

        return $subscription->fresh() ?? $subscription;
    }
}
