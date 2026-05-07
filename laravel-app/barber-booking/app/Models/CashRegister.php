<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegister extends Model
{
    protected $fillable = [
        'barber_shop_id',
        'date',
        'opening_amount',
        'cash_income_amount',
        'transfer_income_amount',
        'manual_income_amount',
        'expense_amount',
        'system_amount',
        'real_amount',
        'difference_amount',
        'status',
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'opening_amount' => 'decimal:2',
        'cash_income_amount' => 'decimal:2',
        'transfer_income_amount' => 'decimal:2',
        'manual_income_amount' => 'decimal:2',
        'expense_amount' => 'decimal:2',
        'system_amount' => 'decimal:2',
        'real_amount' => 'decimal:2',
        'difference_amount' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function barberShop(): BelongsTo
    {
        return $this->belongsTo(BarberShop::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'barber_shop_id', 'barber_shop_id')
            ->whereDate('confirmed_at', $this->date);
    }

    public function recalculate(): void
    {
        $manualIncome = $this->movements()
            ->where('type', 'income')
            ->sum('amount');

        $expenses = $this->movements()
            ->where('type', 'expense')
            ->sum('amount');

        $transferIncome = Transfer::query()
            ->where('barber_shop_id', $this->barber_shop_id)
            ->where('status', 'confirmed')
            ->whereDate('confirmed_at', $this->date)
            ->sum('amount');

        $cashIncome = Reservation::query()
            ->where('barber_shop_id', $this->barber_shop_id)
            ->where('payment_status', 'pagado')
            ->whereDate('reservation_date', $this->date)
            ->whereNull('transfer_id')
            ->sum('total_amount');

        $systemAmount = (float) $this->opening_amount
            + (float) $cashIncome
            + (float) $transferIncome
            + (float) $manualIncome
            - (float) $expenses;

        $difference = $this->real_amount !== null
            ? (float) $this->real_amount - $systemAmount
            : 0;

        $this->update([
            'cash_income_amount' => $cashIncome,
            'transfer_income_amount' => $transferIncome,
            'manual_income_amount' => $manualIncome,
            'expense_amount' => $expenses,
            'system_amount' => $systemAmount,
            'difference_amount' => $difference,
        ]);
    }

    public function close(float $realAmount, int $userId): void
    {
        $this->update([
            'real_amount' => $realAmount,
            'closed_by' => $userId,
            'closed_at' => now(),
            'status' => 'closed',
        ]);

        $this->recalculate();
    }
}
