<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->cascadeOnDelete();

            $table->string('transaction_id')->nullable();
            $table->string('payer_id')->nullable();
            $table->string('payer_email')->nullable();

            $table->string('payment_method')->default('bank_transfer');
            $table->string('payment_status')->default('pendiente');

            $table->decimal('amount', 10, 2)->nullable();

            $table->string('receipt_image')->nullable();
            $table->text('admin_notes')->nullable();

            $table->json('response_json')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['reservation_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations_details');
    }
};
