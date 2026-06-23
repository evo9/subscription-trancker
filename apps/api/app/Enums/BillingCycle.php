<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\CarbonInterface;

enum BillingCycle: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function perYear(): float
    {
        return match ($this) {
            self::Weekly => 52.0,
            self::Monthly => 12.0,
            self::Quarterly => 4.0,
            self::Yearly => 1.0,
        };
    }

    // addMonthNoOverflow: Jan 31 + 1 month = Feb 28, not Mar 3
    public function advance(CarbonInterface $date): CarbonInterface
    {
        $d = $date->clone();

        return match ($this) {
            self::Weekly => $d->addWeek(),
            self::Monthly => $d->addMonthNoOverflow(),
            self::Quarterly => $d->addMonthsNoOverflow(3),
            self::Yearly => $d->addYear(),
        };
    }
}
