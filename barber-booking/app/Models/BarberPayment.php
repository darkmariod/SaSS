<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarberPayment extends Model
{
    protected $fillable = [
        'barber_shop_id',
        'barber_profile_id',
        'period_start',
        'period_end',
        'gross_amount',
        'commission_percentage',
        'commission_amount',
        'advance_amount',
        'incentive_amount',
        'net_amount',
        'reservations_count',
        'status',
        'calculated_by',
        'paid_by',
        'calculated_at',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'gross_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'incentive_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function barberShop(): BelongsTo
    {
        return $this->belongsTo(BarberShop::class);
    }

    public function barberProfile(): BelongsTo
    {
        return $this->belongsTo(BarberProfile::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function calculate(): void
    {
        $barberProfile = $this->barberProfile;
        if (! $barberProfile) {
            return;
        }

        $reservationsQuery = Reservation::query()
            ->where('barber_shop_id', $this->barber_shop_id)
            ->where('consultant_id', $barberProfile->user_id)
            ->where('payment_status', 'pagado')
            ->whereIn('reservation_status', ['confirmada', 'completada'])
            ->whereDate('reservation_date', '>=', $this->period_start)
            ->whereDate('reservation_date', '<=', $this->period_end);

        $grossAmount = (float) $reservationsQuery->sum('total_amount');
        $reservationsCount = (int) $reservationsQuery->count();

        $commissionPercentage = (float) $barberProfile->commission_percentage;
        $commissionAmount = $grossAmount * ($commissionPercentage / 100);

        $netAmount = $commissionAmount
            - (float) $this->advance_amount
            + (float) $this->incentive_amount;

        $this->update([
            'gross_amount' => $grossAmount,
            'commission_percentage' => $commissionPercentage,
            'commission_amount' => $commissionAmount,
            'net_amount' => $netAmount,
            'reservations_count' => $reservationsCount,
            'status' => 'calculated',
            'calculated_by' => auth()->id(),
            'calculated_at' => now(),
        ]);
    }

    public function markAsPaid(int $userId): void
    {
        $this->update([
            'status' => 'paid',
            'paid_by' => $userId,
            'paid_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }
}
