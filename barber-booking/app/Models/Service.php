<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'barber_shop_id',
        'category',
        'name',
        'description',
        'service_type',
        'parent_service_id',
        'duration_minutes',
        'price',
        'requires_payment',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'requires_payment' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];


    protected static function booted(): void
    {
        // El dueño solo carga nombre, precio y duración. La jerarquía interna
        // (un servicio "principal" que agrupa las opciones reservables) se
        // resuelve sola: pedírsela en el formulario obligaba a entender un
        // concepto del modelo de datos que no le dice nada a un barbero.
        static::saving(function (self $servicio): void {
            if ($servicio->service_type !== 'option' || $servicio->parent_service_id) {
                return;
            }

            $principal = static::where('barber_shop_id', $servicio->barber_shop_id)
                ->where('service_type', 'main')
                ->orderBy('id')
                ->first();

            $principal ??= static::withoutEvents(fn () => static::create([
                'barber_shop_id' => $servicio->barber_shop_id,
                'name' => 'Servicios',
                'category' => $servicio->category ?: 'General',
                'service_type' => 'main',
                'duration_minutes' => 15,
                'price' => 0,
                'is_active' => true,
                'sort_order' => 0,
            ]));

            $servicio->parent_service_id = $principal->id;
        });
    }

    public function barberShop(): BelongsTo
    {
        return $this->belongsTo(BarberShop::class);
    }

    public function parentService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'parent_service_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(Service::class, 'parent_service_id')
            ->where('service_type', 'option')
            ->orderBy('sort_order');
    }

    public function addons(): HasMany
    {
        return $this->hasMany(Service::class, 'parent_service_id')
            ->where('service_type', 'addon')
            ->orderBy('sort_order');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMain($query)
    {
        return $query->where('service_type', 'main');
    }

    public function scopeOptions($query)
    {
        return $query->where('service_type', 'option');
    }

    public function scopeAddons($query)
    {
        return $query->where('service_type', 'addon');
    }
}
