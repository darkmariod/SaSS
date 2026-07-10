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
        // A closed register is a historical snapshot and must never be mutated
        // by later movements, transfers or reservations tied to the same date.
        if ($this->status === 'closed') {
            return;
        }

        $this->computeTotals();
    }

    protected function computeTotals(): void
    {
        $manualIncome = round((float) $this->movements()
            ->where('type', 'income')
            ->sum('amount'), 2);

        $expenses = round((float) $this->movements()
            ->where('type', 'expense')
            ->sum('amount'), 2);

        $transferIncome = round((float) Transfer::query()
            ->where('barber_shop_id', $this->barber_shop_id)
            ->where('status', 'confirmed')
            ->whereDate('confirmed_at', $this->date)
            ->sum('amount'), 2);

        $cashIncome = round((float) Reservation::query()
            ->where('barber_shop_id', $this->barber_shop_id)
            ->where('payment_status', 'pagado')
            ->whereDate('reservation_date', $this->date)
            ->whereNull('transfer_id')
            ->sum('total_amount'), 2);

        $systemAmount = round((float) $this->opening_amount
            + $cashIncome
            + $transferIncome
            + $manualIncome
            - $expenses, 2);

        $difference = $this->real_amount !== null
            ? round((float) $this->real_amount - $systemAmount, 2)
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
        // Closing is idempotent: a register can only be closed once.
        if ($this->status === 'closed') {
            return;
        }

        // Compute the final snapshot while still open, then freeze it.
        $this->real_amount = round($realAmount, 2);
        $this->computeTotals();

        $this->update([
            'real_amount' => round($realAmount, 2),
            'closed_by' => $userId,
            'closed_at' => now(),
            'status' => 'closed',
        ]);
    }
}
