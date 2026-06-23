<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'billing_cycle' => ['sometimes', Rule::enum(BillingCycle::class)],
            'status' => ['sometimes', Rule::enum(SubscriptionStatus::class)],
            'started_at' => ['sometimes', 'date'],
            'next_billing_date' => ['sometimes', 'date', 'after_or_equal:started_at'],
            'category_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('user_id', $user->getKey()),
            ],
            'notify_days_before' => ['sometimes', 'integer', 'min:1', 'max:30'],
        ];
    }
}
