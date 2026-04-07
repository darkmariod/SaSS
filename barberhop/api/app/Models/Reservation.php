<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';

    protected $fillable = [
        'user_id',
        'consultant_id',
        'service_id',
        'reservation_date',
        'start_time',
        'end_time',
        'reservation_status',
        'total_amount',
        'payment_status',
        'cancellation_reason',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // 📅 RELACIONES

    // La reserva pertenece a un usuario (cliente)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // La reserva pertenece a un asesor (consultant)
    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    // La reserva tiene un servicio (cuando exista)
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // La reserva tiene un detalle de pago (uno a uno)
    public function detail()
    {
        return $this->hasOne(ReservationDetail::class, 'reservation_id');
    }
}
