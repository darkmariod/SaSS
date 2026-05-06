<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarberAvailability extends Model
{
    protected $fillable = [
        'barber_profile_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function barberProfile(): BelongsTo
    {
        return $this->belongsTo(BarberProfile::class);
    }
}
