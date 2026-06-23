<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

final class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->getKey() === $subscription->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->getKey() === $subscription->user_id;
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->getKey() === $subscription->user_id;
    }

    public function pause(User $user, Subscription $subscription): bool
    {
        return $user->getKey() === $subscription->user_id;
    }

    public function resume(User $user, Subscription $subscription): bool
    {
        return $user->getKey() === $subscription->user_id;
    }
}
