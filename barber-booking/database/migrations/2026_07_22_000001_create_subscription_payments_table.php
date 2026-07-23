<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Precio anual por plan (prepago). Si es null, se calcula por convención
        // (mensual x 10 = 2 meses gratis) desde el modelo Plan.
        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('annual_price', 10, 2)->nullable()->after('monthly_price');
        });

        // Pago de suscripción de plataforma (owner -> plataforma). Espeja el patrón
        // de `transfers` de las reservas: el owner sube comprobante, el super_admin
        // confirma o rechaza.
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('billing_period'); // monthly | annual
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending | confirmed | rejected
            $table->string('reference')->nullable();
            $table->string('receipt_image_path')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['barber_shop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('annual_price');
        });
    }
};
