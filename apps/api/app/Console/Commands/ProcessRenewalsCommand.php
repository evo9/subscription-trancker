<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Renewals\ProcessDueRenewals;
use Illuminate\Console\Command;

final class ProcessRenewalsCommand extends Command
{
    protected $signature = 'app:process-renewals';

    protected $description = 'Create payments for active subscriptions due today and advance their billing dates.';

    public function handle(ProcessDueRenewals $action): int
    {
        $count = $action->handle();
        $this->info("Processed {$count} subscription(s).");

        return Command::SUCCESS;
    }
}
