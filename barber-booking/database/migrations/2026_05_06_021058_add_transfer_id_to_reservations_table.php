<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'transfer_id')) {
                $table->foreignId('transfer_id')
                    ->nullable()
                    ->after('payment_status')
                    ->constrained('transfers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'transfer_id')) {
                $table->dropConstrainedForeignId('transfer_id');
            }
        });
    }
};
