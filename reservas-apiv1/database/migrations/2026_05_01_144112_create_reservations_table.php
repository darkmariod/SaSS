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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultant_id');
            $table->unsignedBigInteger('client_id');
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_amount', 8, 2);
            $table->string('reservation_status')->default('pendiente');
            $table->string('payment_status')->default('pendiente');
            $table->timestamps();
            
            $table->foreign('consultant_id')->references('id')->on('users');
            $table->foreign('client_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
