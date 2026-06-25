<?php

declare(strict_types=1);

namespace App\Actions\Renewals;

use App\Jobs\SendRenewalReminderJob;
use App\Models\Subscription;

final class SendDueReminders
{
    public function handle(): int
    {
        $dispatched = 0;

        Subscription::query()
            ->active()
            ->dueForReminder()
            ->each(function (Subscription $subscription) use (&$dispatched): void {
                SendRenewalReminderJob::dispatch($subscription);
                $dispatched++;
            });

        return $dispatched;
    }
}
