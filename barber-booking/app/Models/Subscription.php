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
        return $this->status === 'expired'
            || ($this->status === 'trial' && $this->trial_ends_at && Carbon::now()->greaterThan($this->trial_ends_at));
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
