<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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


    /**
     * El slug es la dirección pública de la barbería (/barberia/{slug}) y suele
     * quedar impresa en un código QR pegado en la puerta, así que se normaliza
     * siempre al asignarlo.
     *
     * Esto existe por un fallo real: al renombrar la barbería desde el panel se
     * guardó el slug "Seven Barber " —con mayúsculas y un espacio final—, la
     * dirección pública empezó a responder 404 y durante días nadie pudo
     * reservar. Normalizar en el modelo cubre todas las vías de escritura, no
     * sólo el formulario donde apareció el problema.
     */

    protected static function booted(): void
    {
        // Al cambiar la dirección pública se conserva la anterior, para que un
        // código QR ya impreso siga funcionando en lugar de caer en un 404.
        static::updating(function (self $barberia): void {
            $anterior = $barberia->getOriginal('slug');

            if (! $anterior || $anterior === $barberia->slug) {
                return;
            }

            DB::table('barber_shop_slug_history')->updateOrInsert(
                ['slug' => $anterior],
                ['barber_shop_id' => $barberia->id, 'created_at' => now()],
            );
        });
    }

    /**
     * Busca una barbería por su dirección actual o por cualquiera que haya
     * usado antes. Devuelve null si esa dirección nunca existió.
     */
    public static function porSlugHistorico(string $slug): ?self
    {
        $actual = static::where('slug', $slug)->first();

        if ($actual) {
            return $actual;
        }

        $id = DB::table('barber_shop_slug_history')->where('slug', $slug)->value('barber_shop_id');

        return $id ? static::find($id) : null;
    }

    public function setSlugAttribute(?string $valor): void
    {
        $limpio = Str::slug((string) $valor);

        if ($limpio === '') {
            $limpio = Str::slug((string) ($this->attributes['name'] ?? '')) ?: 'barberia';
        }

        $this->attributes['slug'] = $limpio;
    }

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

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }
}
