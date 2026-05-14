<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'cotizacion_id',
        'name',
        'event_date',
        'location',
        'bartender_name',
        'status',
        'notes',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
