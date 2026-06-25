<?php

declare(strict_types=1);

namespace App\Actions\Stats;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class GetUpcomingRenewals
{
    /**
     * @return Collection<int, Subscription>
     */
    public function handle(User $user, int $days = 30): Collection
    {
        return Subscription::query()
            ->forUser($user)
            ->active()
            ->dueWithin($days)
            ->with('category')
            ->orderBy('next_billing_date')
            ->get();
    }
}
