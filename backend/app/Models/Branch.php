<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'phone', 'email', 'timezone', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function professionals()
    {
        return $this->belongsToMany(Professional::class, 'branch_professional')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }

    public function schedules()
    {
        return $this->hasManyThrough(Schedule::class, 'branch_professional', 'branch_id', 'professional_id', 'id', 'professional_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
