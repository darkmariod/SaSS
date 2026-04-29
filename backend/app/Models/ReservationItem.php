<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReservationItem extends Model
{
    use HasFactory;

    protected $fillable = ['reservation_id', 'service_id', 'professional_service_id', 'price', 'duration_minutes'];

    protected $casts = ['price' => 'decimal:2', 'duration_minutes' => 'integer'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function professionalService()
    {
        return $this->belongsTo(ProfessionalService::class);
    }
}
