<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'consultant_id',
        'name',
        'description',
        'price_per_hour',
        'duration_minutes',
        'is_active'
    ];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
        'is_active' => 'boolean',
        'duration_minutes' => 'integer'
    ];

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'service_id');
    }
}
