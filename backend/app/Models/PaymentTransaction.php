<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['payment_id', 'transaction_id', 'status', 'response_json'];

    protected $casts = ['response_json' => 'array'];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
