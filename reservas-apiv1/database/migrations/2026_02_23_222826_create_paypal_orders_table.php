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
        Schema::create('paypal_orders', function (Blueprint $table) {
            $table->id();

            // UUID que enviaremos en custom_id
            $table->uuid('reference')->unique();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('consultant_id');

            $table->date('reservation_date');

            // Guardaremos horas como JSON
            $table->json('hours');

            $table->decimal('total_amount', 10, 2);

            // Order ID real de PayPal
            $table->string('paypal_order_id')->nullable();

            $table->enum('status', [
                'pending',
                'completed',
                'cancelled',
                'expired'
            ])->default('pending');

            $table->timestamps();

            // Relaciones
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('consultant_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paypal_orders');
    }
};
