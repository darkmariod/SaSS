<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('consultant_id');
            
            $table->foreign('service_id')
                  ->references('id')
                  ->on('services')
                  ->onDelete('set null');
            
            // Index for conflict prevention queries
            $table->index(['consultant_id', 'reservation_date', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
            $table->dropIndex(['consultant_id', 'reservation_date', 'start_time']);
        });
    }
};
