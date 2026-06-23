<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Payment
 */
final class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Carbon $paidAt */
        $paidAt = $this->paid_at;

        return [
            'id' => $this->getKey(),
            'subscription_id' => $this->subscription_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'paid_at' => $paidAt->toDateString(),
            'created_at' => $this->created_at,
        ];
    }
}
