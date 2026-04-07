<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationDetail extends Model
{
    protected $table = 'reservations_details';

    protected $fillable = [
        'reservation_id',
        'transaction_id',
        'payer_id',
        'payer_email',
        'payment_status',
        'amount',
        'response_json',
    ];

    // Un detalle pertenece a una reserva
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
