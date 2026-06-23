<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'password',
        ]);

        $streaming = $user->categories()->where('name', 'Streaming')->firstOrFail();
        $software = $user->categories()->where('name', 'Software')->firstOrFail();
        $hosting = $user->categories()->where('name', 'Hosting')->firstOrFail();
        $fitness = $user->categories()->where('name', 'Fitness')->firstOrFail();
        $other = $user->categories()->where('name', 'Other')->firstOrFail();

        // --- Active subscriptions covering all 4 billing cycles ---

        $netflix = Subscription::factory()->for($user)->active()->create([
            'category_id' => $streaming->id,
            'name' => 'Netflix',
            'price' => 199.00,
            'billing_cycle' => BillingCycle::Monthly,
            'started_at' => now()->subMonths(12),
            'next_billing_date' => now()->addDays(14),
        ]);

        $github = Subscription::factory()->for($user)->active()->create([
            'category_id' => $software->id,
            'name' => 'GitHub Pro',
            'price' => 1100.00,
            'billing_cycle' => BillingCycle::Yearly,
            'started_at' => now()->subYear(),
            'next_billing_date' => now()->addMonths(6),
        ]);

        $digitalocean = Subscription::factory()->for($user)->active()->create([
            'category_id' => $hosting->id,
            'name' => 'DigitalOcean',
            'price' => 600.00,
            'billing_cycle' => BillingCycle::Monthly,
            'started_at' => now()->subMonths(8),
            'next_billing_date' => now()->addDays(20),
        ]);

        $adobe = Subscription::factory()->for($user)->active()->create([
            'category_id' => $software->id,
            'name' => 'Adobe Creative Cloud',
            'price' => 2100.00,
            'billing_cycle' => BillingCycle::Quarterly,
            'started_at' => now()->subMonths(9),
            'next_billing_date' => now()->addMonths(2),
        ]);

        $gym = Subscription::factory()->for($user)->active()->create([
            'category_id' => $fitness->id,
            'name' => 'Gym Classes',
            'price' => 350.00,
            'billing_cycle' => BillingCycle::Weekly,
            'started_at' => now()->subMonths(3),
            'next_billing_date' => now()->addDays(6),
        ]);

        // --- Due-soon subscriptions (next_billing_date in 1–2 days) ---

        Subscription::factory()->for($user)->dueSoon()->create([
            'category_id' => $streaming->id,
            'name' => 'Spotify',
            'price' => 179.00,
            'billing_cycle' => BillingCycle::Monthly,
            'started_at' => now()->subMonths(6),
            'next_billing_date' => now()->addDays(1),
        ]);

        Subscription::factory()->for($user)->dueSoon()->create([
            'category_id' => $other->id,
            'name' => 'Notion',
            'price' => 480.00,
            'billing_cycle' => BillingCycle::Monthly,
            'started_at' => now()->subMonths(4),
            'next_billing_date' => now()->addDays(2),
        ]);

        // --- Paused subscriptions ---

        Subscription::factory()->for($user)->paused()->create([
            'category_id' => $hosting->id,
            'name' => 'AWS S3',
            'price' => 450.00,
            'billing_cycle' => BillingCycle::Monthly,
            'started_at' => now()->subMonths(10),
        ]);

        Subscription::factory()->for($user)->paused()->create([
            'category_id' => $software->id,
            'name' => 'JetBrains All Products',
            'price' => 3200.00,
            'billing_cycle' => BillingCycle::Yearly,
            'started_at' => now()->subMonths(14),
        ]);

        // --- Cancelled subscriptions ---

        Subscription::factory()->for($user)->cancelled()->create([
            'category_id' => $streaming->id,
            'name' => 'YouTube Premium',
            'price' => 149.00,
            'billing_cycle' => BillingCycle::Monthly,
            'started_at' => now()->subMonths(18),
        ]);

        Subscription::factory()->for($user)->cancelled()->create([
            'category_id' => $fitness->id,
            'name' => 'Planet Fitness',
            'price' => 1200.00,
            'billing_cycle' => BillingCycle::Yearly,
            'started_at' => now()->subYears(2),
        ]);

        // --- Payment history (past periods for each active subscription) ---

        $this->seedPayments($netflix, 6);
        $this->seedPayments($github, 2);       // yearly — 2 years of history
        $this->seedPayments($digitalocean, 6);
        $this->seedPayments($adobe, 4);        // quarterly — 4 quarters
        $this->seedPayments($gym, 6);          // weekly — 6 weeks
    }

    private function seedPayments(Subscription $subscription, int $periods): void
    {
        for ($i = 1; $i <= $periods; $i++) {
            $date = match ($subscription->billing_cycle) {
                BillingCycle::Weekly => $subscription->next_billing_date->clone()->subWeeks($i),
                BillingCycle::Monthly => $subscription->next_billing_date->clone()->subMonths($i),
                BillingCycle::Quarterly => $subscription->next_billing_date->clone()->subMonths($i * 3),
                BillingCycle::Yearly => $subscription->next_billing_date->clone()->subYears($i),
            };

            Payment::factory()->create([
                'subscription_id' => $subscription->id,
                'amount' => $subscription->price,
                'currency' => $subscription->currency,
                'paid_at' => $date,
            ]);
        }
    }
}
