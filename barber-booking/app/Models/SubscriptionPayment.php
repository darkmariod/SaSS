<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * A platform subscription payment (owner → platform). Mirrors the reservation
 * `Transfer` pattern: the owner submits a transfer, the super_admin confirms or
 * rejects it. Confirming activates/extends the barbershop's subscription.
 */
class SubscriptionPayment extends Model
{
    protected $fillable = [
        'barber_shop_id',
        'subscription_id',
        'plan_id',
        'billing_period',
        'amount',
        'status',
        'reference',
        'receipt_image_path',
        'confirmed_by',
        'confirmed_at',
        'period_start',
        'period_end',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];

    public function barberShop(): BelongsTo
    {
        return $this->belongsTo(BarberShop::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_image_path ? asset('storage/' . $this->receipt_image_path) : null;
    }

    /**
     * Confirm the payment and extend the tenant's subscription for the paid
     * period. Idempotent: a payment can only be confirmed once.
     */
    public function confirm(int $userId): void
    {
        if ($this->status === 'confirmed') {
            return;
        }

        DB::transaction(function () use ($userId): void {
            $subscription = $this->subscription ?? $this->barberShop->subscription;

            if ($subscription) {
                $subscription->activate($this->billing_period);
                $this->subscription_id = $subscription->id;
            }

            $this->update([
                'subscription_id' => $this->subscription_id,
                'status' => 'confirmed',
                'confirmed_by' => $userId,
                'confirmed_at' => now(),
                'period_start' => $subscription?->starts_at ?? now(),
                'period_end' => $subscription?->ends_at,
            ]);
        });
    }

    public function reject(int $userId): void
    {
        if ($this->status === 'confirmed') {
            return;
        }

        $this->update([
            'status' => 'rejected',
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);
    }
}
