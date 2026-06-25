<?php

declare(strict_types=1);

namespace App\Actions\Stats;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Collection;

final class BuildStatsSummary
{
    /**
     * @return array{monthly_total: float, yearly_total: float, by_category: list<array<string, mixed>>}
     */
    public function handle(User $user): array
    {
        /** @var Collection<int, Subscription> $subscriptions */
        $subscriptions = Subscription::query()
            ->forUser($user)
            ->active()
            ->with('category')
            ->get();

        $byCategory = $subscriptions
            ->groupBy(fn (Subscription $s): string => (string) ($s->category_id ?? ''))
            ->map(function (Collection $group): array {
                /** @var Subscription $first */
                $first = $group->first();

                return [
                    'category_id' => $first->category?->getKey(),
                    'name' => $first->category?->name,
                    'color' => $first->category?->color,
                    'monthly_total' => round((float) $group->sum('monthly_cost'), 2),
                    'yearly_total' => round((float) $group->sum('yearly_cost'), 2),
                ];
            })
            ->values()
            ->all();

        return [
            'monthly_total' => round((float) $subscriptions->sum('monthly_cost'), 2),
            'yearly_total' => round((float) $subscriptions->sum('yearly_cost'), 2),
            'by_category' => $byCategory,
        ];
    }
}
