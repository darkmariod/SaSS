<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'cotizacion_id',
        'evento_id',
        'amount',
        'payment_method',
        'paid_at',
        'reference',
        'status',
        'notes',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}
