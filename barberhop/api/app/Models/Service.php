<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'price', 'duration'];

    // Un servicio puede ser oferecido por muchos asesores (many-to-many)
    public function consultants()
    {
        return $this->belongsToMany(User::class, 'consultant_service', 'service_id', 'consultant_id');
    }

    // Un servicio puede tener muchas reservas
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
