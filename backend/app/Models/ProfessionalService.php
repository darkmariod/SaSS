<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfessionalService extends Model
{
    use HasFactory;

    protected $table = 'professional_service';

    protected $fillable = ['professional_id', 'service_id', 'price', 'is_active'];

    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
