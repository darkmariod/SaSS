<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultantAvailability extends Model
{
    use HasFactory;

    protected $table = 'consultant_availability';

    protected $fillable = [
        'consultant_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available'
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_available' => 'boolean'
    ];

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }
}
