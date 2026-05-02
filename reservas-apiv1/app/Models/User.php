<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'nombres',
        'apellidos',
        'telefono',
        'rol_id',
        'email',
        'foto',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    public function reservationsAsClient()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    public function reservationsAsConsultant()
    {
        return $this->hasMany(Reservation::class, 'consultant_id');
    }
}
