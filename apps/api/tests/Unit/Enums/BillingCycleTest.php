<?php

declare(strict_types=1);

use App\Enums\BillingCycle;
use Carbon\Carbon;

describe('BillingCycle', function (): void {
    describe('perYear', function (): void {
        it('returns 52 for weekly', fn () => expect(BillingCycle::Weekly->perYear())->toBe(52.0));
        it('returns 12 for monthly', fn () => expect(BillingCycle::Monthly->perYear())->toBe(12.0));
        it('returns 4 for quarterly', fn () => expect(BillingCycle::Quarterly->perYear())->toBe(4.0));
        it('returns 1 for yearly', fn () => expect(BillingCycle::Yearly->perYear())->toBe(1.0));
    });

    describe('advance', function (): void {
        it('adds one week for weekly', function (): void {
            $date = Carbon::create(2024, 1, 15);
            expect(BillingCycle::Weekly->advance($date)->toDateString())->toBe('2024-01-22');
        });

        it('adds one month with no overflow for monthly', function (): void {
            $date = Carbon::create(2023, 1, 31);
            expect(BillingCycle::Monthly->advance($date)->toDateString())->toBe('2023-02-28');
        });

        it('adds three months with no overflow for quarterly', function (): void {
            $date = Carbon::create(2023, 1, 31);
            expect(BillingCycle::Quarterly->advance($date)->toDateString())->toBe('2023-04-30');
        });

        it('adds one year for yearly', function (): void {
            $date = Carbon::create(2024, 1, 15);
            expect(BillingCycle::Yearly->advance($date)->toDateString())->toBe('2025-01-15');
        });

        it('does not mutate the input date', function (): void {
            $date = Carbon::create(2024, 3, 15);
            $original = $date->toDateString();
            BillingCycle::Weekly->advance($date);
            expect($date->toDateString())->toBe($original);
        });
    });
});
