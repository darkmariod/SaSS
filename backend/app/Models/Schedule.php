<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['professional_id', 'branch_id', 'day_of_week', 'start_time', 'end_time', 'is_active'];

    protected $casts = [
        'day_of_week' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean'
    ];

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
