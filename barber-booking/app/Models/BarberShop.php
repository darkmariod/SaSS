<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarberShop extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'city',
        'address',
        'phone',
        'instagram',
        'tiktok',
        'facebook',
        'whatsapp',
        'bank_name',
        'bank_account',
        'bank_account_owner',
        'bank_qr_image',
        'payment_instructions',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'name' => 'string',
        'description' => 'string',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function barbers(): HasMany
    {
        return $this->hasMany(BarberProfile::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function cashRegisters(): HasMany
    {
        return $this->hasMany(CashRegister::class);
    }

    public function barberPayments(): HasMany
    {
        return $this->hasMany(BarberPayment::class);
    }
}
