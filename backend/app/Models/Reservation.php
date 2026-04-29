<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'user_id',
        'branch_id',
        'professional_id',
        'reservation_date',
        'start_time',
        'end_time',
        'reservation_status',
        'payment_status',
        'total_amount',
        'cancellation_reason'
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'total_amount' => 'decimal:2'
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function items()
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
