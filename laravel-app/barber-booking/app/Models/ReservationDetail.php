<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationDetail extends Model
{
    protected $table = 'reservations_details';

    protected $fillable = [
        'reservation_id',
        'transaction_id',
        'payer_id',
        'payer_email',
        'payment_method',
        'payment_status',
        'amount',
        'receipt_image',
        'admin_notes',
        'response_json',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'response_json' => 'array',
        'paid_at' => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
