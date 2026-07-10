<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class BarberPayment extends Model
{
    protected static function booted(): void
    {
        // If a payment is deleted while it still owns reservations, release them
        // so they become payable again instead of staying orphaned.
        static::deleting(function (BarberPayment $payment): void {
            Reservation::query()
                ->where('barber_payment_id', $payment->id)
                ->update(['barber_payment_id' => null]);
        });
    }

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

        DB::transaction(function () use ($barberProfile): void {
            // Release reservations previously tied to this payment so recalculating
            // an existing period does not lose or double-count anything.
            Reservation::query()
                ->where('barber_payment_id', $this->id)
                ->update(['barber_payment_id' => null]);

            // Only reservations not already liquidated by another payment are eligible.
            // This is what prevents daily + weekly + monthly periods from paying twice.
            $reservations = Reservation::query()
                ->where('barber_shop_id', $this->barber_shop_id)
                ->where('consultant_id', $barberProfile->user_id)
                ->where('payment_status', 'pagado')
                ->whereIn('reservation_status', ['confirmada', 'completada'])
                ->whereNull('barber_payment_id')
                ->whereDate('reservation_date', '>=', $this->period_start)
                ->whereDate('reservation_date', '<=', $this->period_end)
                ->get();

            $grossAmount = round((float) $reservations->sum(fn (Reservation $r): float => (float) $r->total_amount), 2);
            $reservationsCount = $reservations->count();

            $commissionPercentage = (float) $barberProfile->commission_percentage;
            $commissionAmount = round($grossAmount * ($commissionPercentage / 100), 2);

            $netAmount = round($commissionAmount
                - (float) $this->advance_amount
                + (float) $this->incentive_amount, 2);

            // Claim these reservations for this payment so no overlapping period can grab them.
            Reservation::query()
                ->whereIn('id', $reservations->pluck('id'))
                ->update(['barber_payment_id' => $this->id]);

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
        });
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
        DB::transaction(function (): void {
            // Free the reservations so they can be included in a future payment.
            Reservation::query()
                ->where('barber_payment_id', $this->id)
                ->update(['barber_payment_id' => null]);

            $this->update([
                'status' => 'cancelled',
            ]);
        });
    }
}
