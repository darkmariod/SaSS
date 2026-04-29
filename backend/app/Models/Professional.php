<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Professional extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'bio', 'photo', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_professional')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'professional_service')
                    ->withPivot('price', 'is_active')
                    ->withTimestamps();
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
