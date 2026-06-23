<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Subscription
 */
final class SubscriptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Carbon $startedAt */
        $startedAt = $this->started_at;
        /** @var Carbon $nextBillingDate */
        $nextBillingDate = $this->next_billing_date;
        /** @var Carbon|null $cancelledAt */
        $cancelledAt = $this->cancelled_at;

        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency,
            'billing_cycle' => $this->billing_cycle->value,
            'status' => $this->status->value,
            'started_at' => $startedAt->toDateString(),
            'next_billing_date' => $nextBillingDate->toDateString(),
            'cancelled_at' => $cancelledAt?->toDateString(),
            'notify_days_before' => $this->notify_days_before,
            'monthly_cost' => $this->monthly_cost,
            'yearly_cost' => $this->yearly_cost,
            'category' => $this->whenLoaded('category', fn () => $this->category === null ? null : [
                'id' => $this->category->getKey(),
                'name' => $this->category->name,
                'color' => $this->category->color,
            ]),
            'payments' => $this->whenLoaded(
                'payments',
                fn (): AnonymousResourceCollection => PaymentResource::collection($this->payments),
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
