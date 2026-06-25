<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:process-renewals')->daily();
Schedule::command('app:send-renewal-reminders')->daily();
