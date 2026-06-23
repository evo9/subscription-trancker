<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Enums\SubscriptionStatus;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\Subscription;
use App\Models\User;

final class CreateSubscription
{
    public function handle(StoreSubscriptionRequest $request, User $user): Subscription
    {
        $startedAt = $request->validated('started_at');

        return Subscription::query()->create([
            'user_id' => $user->getKey(),
            'category_id' => $request->validated('category_id'),
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'price' => $request->validated('price'),
            'currency' => $request->validated('currency'),
            'billing_cycle' => $request->validated('billing_cycle'),
            'status' => $request->validated('status', SubscriptionStatus::Active->value),
            'started_at' => $startedAt,
            'next_billing_date' => $request->validated('next_billing_date') ?? $startedAt,
            'notify_days_before' => $request->validated('notify_days_before', 3),
        ]);
    }
}
