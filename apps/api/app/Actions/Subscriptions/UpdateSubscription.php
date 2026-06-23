<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Http\Requests\UpdateSubscriptionRequest;
use App\Models\Subscription;

final class UpdateSubscription
{
    public function handle(UpdateSubscriptionRequest $request, Subscription $subscription): Subscription
    {
        $subscription->update($request->validated());

        return $subscription->fresh() ?? $subscription;
    }
}
