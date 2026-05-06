<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarberProfile extends Model
{
    protected $fillable = [
        'barber_shop_id',
        'user_id',
        'display_name',
        'bio',
        'photo',
        'commission_percentage',
        'is_active',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function barberShop(): BelongsTo
    {
        return $this->belongsTo(BarberShop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(BarberAvailability::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'consultant_id', 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BarberPayment::class);
    }
}
