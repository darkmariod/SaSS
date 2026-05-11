<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    protected $fillable = [
        'cash_register_id',
        'type',
        'amount',
        'description',
        'performed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (CashMovement $movement): void {
            $movement->cashRegister?->recalculate();
        });

        static::updated(function (CashMovement $movement): void {
            $movement->cashRegister?->recalculate();
        });

        static::deleted(function (CashMovement $movement): void {
            $movement->cashRegister?->recalculate();
        });
    }

    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
