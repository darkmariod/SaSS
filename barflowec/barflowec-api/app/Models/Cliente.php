<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'identification',
        'address',
        'notes',
        'status',
    ];

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }
}
