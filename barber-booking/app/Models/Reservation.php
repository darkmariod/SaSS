<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'barber_shop_id',
        'service_id',
        'addon_service_id',
        'transfer_id',
        'barber_payment_id',
        'google_event_id',
        'upload_token',
        'user_id',
        'consultant_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_date',
        'start_time',
        'end_time',
        'reservation_status',
        'total_amount',
        'payment_status',
        'cancellation_reason',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * The upload token is a capability secret for the public receipt upload.
     * It must never leak in serialized payloads (calendar, barber panel, etc.).
     */
    protected $hidden = [
        'upload_token',
    ];

    public function barberShop(): BelongsTo
    {
        return $this->belongsTo(BarberShop::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function addonService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'addon_service_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function detail(): HasOne
    {
        return $this->hasOne(ReservationDetail::class);
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function barberPayment(): BelongsTo
    {
        return $this->belongsTo(BarberPayment::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'reservation_status' => 'completada',
        ]);
    }

    public function cancel(?string $reason = null): void
    {
        $this->update([
            'reservation_status' => 'cancelada',
            'cancellation_reason' => $reason,
        ]);
    }
}
