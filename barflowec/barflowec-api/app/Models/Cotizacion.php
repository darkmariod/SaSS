<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'cliente_id',
        'quote_number',
        'event_type',
        'event_date',
        'guests',
        'subtotal',
        'tax',
        'total',
        'status',
        'notes',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
