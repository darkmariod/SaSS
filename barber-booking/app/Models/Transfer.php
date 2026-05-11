<?php

namespace App\Models;

use App\Models\CashRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    protected $fillable = [
        'barber_shop_id',
        'reservation_id',
        'reference',
        'amount',
        'status',
        'receipt_image_path',
        'confirmed_by',
        'confirmed_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function barberShop(): BelongsTo
    {
        return $this->belongsTo(BarberShop::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    private function recalculateCashRegister(): void
    {
        $date = $this->confirmed_at?->toDateString() ?? today()->toDateString();

        CashRegister::query()
            ->where('barber_shop_id', $this->barber_shop_id)
            ->whereDate('date', $date)
            ->first()
            ?->recalculate();
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (! $this->receipt_image_path) {
            return null;
        }

        return asset('storage/' . $this->receipt_image_path);
    }

    public function confirm(int $userId): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);

        if ($this->reservation) {
            $this->reservation->update([
                'payment_status' => 'pagado',
                'reservation_status' => 'confirmada',
                'transfer_id' => $this->id,
            ]);

            $this->reservation->detail?->update([
                'payment_status' => 'pagado',
                'paid_at' => now(),
            ]);
        }

        $this->recalculateCashRegister();
    }

    public function reject(int $userId): void
    {
        $this->update([
            'status' => 'rejected',
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);

        if ($this->reservation) {
            $this->reservation->update([
                'payment_status' => 'rechazado',
                'reservation_status' => 'cancelada',
                'transfer_id' => $this->id,
            ]);

            $this->reservation->detail?->update([
                'payment_status' => 'rechazado',
            ]);
        }

        $this->recalculateCashRegister();
    }
}
