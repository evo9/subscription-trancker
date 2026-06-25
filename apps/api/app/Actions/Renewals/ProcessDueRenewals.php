<?php

declare(strict_types=1);

namespace App\Actions\Renewals;

use App\Events\SubscriptionRenewed;
use App\Models\Payment;
use App\Models\Subscription;

final class ProcessDueRenewals
{
    public function handle(): int
    {
        $processed = 0;

        Subscription::query()
            ->active()
            ->dueForRenewal()
            ->each(function (Subscription $subscription) use (&$processed): void {
                if ($subscription->payments()->whereDate('paid_at', $subscription->next_billing_date)->exists()) {
                    return;
                }

                Payment::query()->create([
                    'subscription_id' => $subscription->getKey(),
                    'amount' => $subscription->price,
                    'currency' => $subscription->currency,
                    'paid_at' => $subscription->next_billing_date,
                ]);

                $subscription->update([
                    'next_billing_date' => $subscription->billing_cycle->advance($subscription->next_billing_date),
                ]);

                SubscriptionRenewed::dispatch($subscription);

                $processed++;
            });

        return $processed;
    }
}
