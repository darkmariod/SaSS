<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'max_barbers',
        'setup_price',
        'monthly_price',
        'annual_price',
        'trial_days',
        'is_active',
    ];

    protected $casts = [
        'max_barbers' => 'integer',
        'setup_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Price for a billing period. Annual falls back to "10 months" (2 free) when
     * no explicit annual_price is set, so a plan is always sellable annually.
     */
    public function priceFor(string $period): float
    {
        if ($period === 'annual') {
            return (float) ($this->annual_price ?? round((float) $this->monthly_price * 10, 2));
        }

        return (float) $this->monthly_price;
    }
}
