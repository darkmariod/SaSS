<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayPalOrder extends Model
{
    use HasFactory;

    protected $table = 'paypal_orders';

    protected $fillable = [
        'reference',
        'user_id',
        'consultant_id',
        'reservation_date',
        'hours',
        'total_amount',
        'paypal_order_id',
        'status'
    ];

    protected $casts = [
        'hours' => 'array',
        'reservation_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }
}
