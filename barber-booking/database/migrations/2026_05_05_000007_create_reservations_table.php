<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barber_shop_id')
                ->constrained('barber_shops')
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('consultant_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();

            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');

            $table->enum('reservation_status', [
                'pendiente',
                'confirmada',
                'cancelada',
                'completada',
                'no_asistio'
            ])->default('pendiente');

            $table->decimal('total_amount', 10, 2)->default(0);

            $table->enum('payment_status', [
                'pendiente',
                'comprobante_subido',
                'pagado',
                'rechazado',
                'fallido'
            ])->default('pendiente');

            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['barber_shop_id', 'reservation_date']);
            $table->index(['consultant_id', 'reservation_date']);
            $table->index(['reservation_status', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
