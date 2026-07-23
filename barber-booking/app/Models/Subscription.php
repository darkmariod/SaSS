<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'barber_shop_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancelled_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function barberShop(): BelongsTo
    {
        return $this->belongsTo(BarberShop::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['trial', 'active']);
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if ($this->status === 'trial') {
            return $this->trial_ends_at && Carbon::now()->greaterThan($this->trial_ends_at);
        }

        // A paid subscription lapses once its paid period (ends_at) is over.
        if ($this->status === 'active') {
            return $this->ends_at && Carbon::now()->greaterThan($this->ends_at);
        }

        return false;
    }

    /**
     * Activate (or extend) the subscription for a billing period. Extends from
     * the current ends_at when still in the future, so paying early stacks time
     * instead of losing the remaining days.
     *
     * @param  'monthly'|'annual'  $period
     */
    public function activate(string $period): void
    {
        $from = ($this->ends_at && $this->ends_at->isFuture()) ? $this->ends_at : Carbon::now();

        $newEnd = $period === 'annual'
            ? $from->copy()->addYear()
            : $from->copy()->addMonth();

        $this->update([
            'status' => 'active',
            'starts_at' => $this->starts_at ?? Carbon::now(),
            'ends_at' => $newEnd,
            'cancelled_at' => null,
        ]);
    }

    public function daysRemainingInTrial(): int
    {
        if (! $this->trial_ends_at || $this->status !== 'trial') {
            return 0;
        }

        return max(0, Carbon::now()->diffInDays(Carbon::parse($this->trial_ends_at), false));
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['trial', 'active']);
    }
}
