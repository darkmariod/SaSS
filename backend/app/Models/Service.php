<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'duration_minutes', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'duration_minutes' => 'integer'];

    public function professionals()
    {
        return $this->belongsToMany(Professional::class, 'professional_service')
                    ->withPivot('price', 'is_active')
                    ->withTimestamps();
    }
}
