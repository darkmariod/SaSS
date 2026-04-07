<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nombres',
        'apellidos',
        'teléfono',
        'rol_id',
        'email',
        'foto',
        'email_verified_at',
        'password',
        'remember_token',
        'deleted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    // 👤 RELACIONES

    // Un usuario tiene un rol (belongsTo)
    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    // Un usuario puede tener muchas reservas como cliente (hasMany)
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    // Un usuario (asesor) puede tener muchas reservas atendidas (hasMany)
    public function consultantReservations()
    {
        return $this->hasMany(Reservation::class, 'consultant_id');
    }

    // Un asesor tiene muchos horarios (cuando existan tablas)
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'consultant_id');
    }

    // Un asesor puede ofrecer muchos servicios (relación many-to-many)
    public function services()
    {
        return $this->belongsToMany(Service::class, 'consultant_service', 'consultant_id', 'service_id');
    }
}
