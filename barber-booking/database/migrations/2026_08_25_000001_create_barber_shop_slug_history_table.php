<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial de direcciones públicas de cada barbería.
 *
 * La dirección termina impresa en un código QR pegado en la puerta. Cuando el
 * dueño renombra la barbería y cambia el slug, ese papel queda apuntando a una
 * página inexistente y los clientes ven un 404 sin que nadie se entere. Ya pasó
 * dos veces en producción.
 *
 * Guardando las direcciones anteriores, las viejas siguen llevando a la
 * barbería correcta para siempre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barber_shop_slug_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_shop_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_shop_slug_history');
    }
};
