<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cada barbero sincroniza contra su propio Google Calendar.
 *
 * - barber_profiles.google_calendar_id: el calendario configurado para ese
 *   barbero. Vacío significa "usar el calendario general de la barbería"
 *   (services.google.calendar_id).
 * - reservations.google_calendar_id: el calendario donde el evento quedó
 *   realmente creado. Se guarda porque la configuración del barbero puede
 *   cambiar después: sin este dato, editar o cancelar una reserva vieja
 *   apuntaría al calendario nuevo y el evento original quedaría huérfano.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barber_profiles', function (Blueprint $table) {
            $table->string('google_calendar_id')->nullable()->after('photo');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('google_calendar_id')->nullable()->after('google_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('barber_profiles', function (Blueprint $table) {
            $table->dropColumn('google_calendar_id');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('google_calendar_id');
        });
    }
};
