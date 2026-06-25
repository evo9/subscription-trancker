<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @use HasFactory<SubscriptionFactory>
 *
 * @property BillingCycle $billing_cycle
 * @property SubscriptionStatus $status
 * @property Carbon $next_billing_date
 * @property-read float $monthly_cost
 * @property-read float $yearly_cost
 *
 * @method static Builder<self> forUser(User $user)
 * @method static Builder<self> active()
 * @method static Builder<self> dueWithin(int $days)
 * @method static Builder<self> dueForRenewal()
 * @method static Builder<self> dueForReminder()
 */
#[Fillable([
    'user_id',
    'category_id',
    'name',
    'description',
    'price',
    'currency',
    'billing_cycle',
    'status',
    'started_at',
    'next_billing_date',
    'cancelled_at',
    'notify_days_before',
])]
class Subscription extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'billing_cycle' => BillingCycle::class,
            'status' => SubscriptionStatus::class,
            'price' => 'decimal:2',
            'started_at' => 'date',
            'next_billing_date' => 'date',
            'cancelled_at' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->getKey());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::Active);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * Subscriptions whose next_billing_date falls between today and today+$days (inclusive).
     */
    public function scopeDueWithin(Builder $query, int $days): Builder
    {
        return $query->whereBetween('next_billing_date', [
            now()->toDateString(),
            now()->addDays($days)->toDateString(),
        ]);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeDueForRenewal(Builder $query): Builder
    {
        return $query->whereDate('next_billing_date', '<=', now()->toDateString());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     *
     * Matches each subscription's own notify_days_before window using a column expression.
     */
    public function scopeDueForReminder(Builder $query): Builder
    {
        /** @var Connection $connection */
        $connection = $query->getConnection();
        $upper = $connection->getDriverName() === 'sqlite'
            ? "date('now', '+' || notify_days_before || ' days')"
            : "CURRENT_DATE + (notify_days_before * INTERVAL '1 day')";

        return $query->whereRaw(
            "next_billing_date BETWEEN CURRENT_DATE AND {$upper}",
        );
    }

    /** @return Attribute<float, never> */
    public function monthlyCost(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round((float) $this->price * $this->billing_cycle->perYear() / 12, 2),
        );
    }

    /** @return Attribute<float, never> */
    public function yearlyCost(): Attribute
    {
        return Attribute::make(
            get: fn (): float => round((float) $this->price * $this->billing_cycle->perYear(), 2),
        );
    }
}
