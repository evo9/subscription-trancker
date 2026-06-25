<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Renewals\SendDueReminders;
use Illuminate\Console\Command;

final class SendRenewalRemindersCommand extends Command
{
    protected $signature = 'app:send-renewal-reminders';

    protected $description = 'Dispatch renewal reminder jobs for subscriptions due within their notification window.';

    public function handle(SendDueReminders $action): int
    {
        $count = $action->handle();
        $this->info("Dispatched {$count} reminder job(s).");

        return Command::SUCCESS;
    }
}
