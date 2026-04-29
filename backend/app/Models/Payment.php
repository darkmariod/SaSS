<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['reservation_id', 'payment_method_id', 'reference', 'amount', 'status'];

    protected $casts = ['amount' => 'decimal:2'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
