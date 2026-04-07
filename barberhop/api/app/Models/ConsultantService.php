<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultantService extends Model
{
    protected $table = 'consultant_service';

    protected $fillable = [
        'consultant_id',
        'service_id',
    ];

    // Un registro pertenece a un asesor
    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    // Un registro pertenece a un servicio
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
