<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El recordatorio se manda la tarde anterior a la cita. Sin dejar constancia de
 * cuándo salió, cada corrida del programador volvería a enviarlo y el cliente
 * recibiría el mismo aviso varias veces.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('google_calendar_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('reminder_sent_at');
        });
    }
};
