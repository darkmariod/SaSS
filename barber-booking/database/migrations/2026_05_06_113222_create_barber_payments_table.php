<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barber_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barber_shop_id')
                ->constrained('barber_shops')
                ->cascadeOnDelete();
            $table->foreignId('barber_profile_id')
                ->constrained('barber_profiles')
                ->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('gross_amount', 10, 2)->default(0);
            $table->decimal('commission_percentage', 5, 2)->default(50);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('advance_amount', 10, 2)->default(0);
            $table->decimal('incentive_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->unsignedInteger('reservations_count')->default(0);
            $table->enum('status', [
                'draft',
                'calculated',
                'paid',
                'cancelled',
            ])->default('draft');
            $table->foreignId('calculated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('paid_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['barber_shop_id', 'status']);
            $table->index(['barber_profile_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_payments');
    }
};
