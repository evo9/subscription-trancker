<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\RenewalReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class SendRenewalReminderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Subscription $subscription,
    ) {}

    public function handle(): void
    {
        /** @var User $user */
        $user = $this->subscription->user;
        $user->notify(new RenewalReminder($this->subscription));
    }
}
