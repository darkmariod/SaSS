<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $table = 'users';

    protected $fillable = [
        'nombres',
        'apellidos',
        'teléfono',
        'rol_id',
        'email',
        'foto',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    // Reservas como cliente
    public function reservationsAsClient()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    // Reservas como consultor
    public function reservationsAsConsultant()
    {
        return $this->hasMany(Reservation::class, 'consultant_id');
    }

    public function professional()
    {
        return $this->hasOne(Professional::class);
    }

    public function clientReservations()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }
}
