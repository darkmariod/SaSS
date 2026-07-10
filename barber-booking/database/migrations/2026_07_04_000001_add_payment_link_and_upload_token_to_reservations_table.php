<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Ties a reservation to the barber payment that liquidated it.
            // Prevents overlapping periods (daily/weekly/monthly) from paying twice.
            if (! Schema::hasColumn('reservations', 'barber_payment_id')) {
                $table->foreignId('barber_payment_id')
                    ->nullable()
                    ->after('transfer_id')
                    ->constrained('barber_payments')
                    ->nullOnDelete();
            }

            // Capability secret for the public receipt upload endpoint.
            if (! Schema::hasColumn('reservations', 'upload_token')) {
                $table->string('upload_token', 64)
                    ->nullable()
                    ->after('google_event_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'barber_payment_id')) {
                $table->dropConstrainedForeignId('barber_payment_id');
            }

            if (Schema::hasColumn('reservations', 'upload_token')) {
                $table->dropColumn('upload_token');
            }
        });
    }
};
