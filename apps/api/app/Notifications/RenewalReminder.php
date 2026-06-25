<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class RenewalReminder extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Subscription $subscription,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Upcoming renewal: {$this->subscription->name}")
            ->line("Your subscription to **{$this->subscription->name}** renews on {$this->subscription->next_billing_date->toFormattedDateString()}.")
            ->line("Amount: {$this->subscription->price} {$this->subscription->currency}");
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->getKey(),
            'subscription_name' => $this->subscription->name,
            'amount' => $this->subscription->price,
            'currency' => $this->subscription->currency,
            'next_billing_date' => $this->subscription->next_billing_date->toDateString(),
        ];
    }
}
