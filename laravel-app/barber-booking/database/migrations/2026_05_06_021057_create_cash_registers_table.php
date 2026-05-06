<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barber_shop_id')
                ->constrained('barber_shops')
                ->cascadeOnDelete();

            $table->date('date');

            $table->decimal('opening_amount', 10, 2)->default(0);

            $table->decimal('cash_income_amount', 10, 2)->default(0);
            $table->decimal('transfer_income_amount', 10, 2)->default(0);
            $table->decimal('manual_income_amount', 10, 2)->default(0);
            $table->decimal('expense_amount', 10, 2)->default(0);

            $table->decimal('system_amount', 10, 2)->default(0);
            $table->decimal('real_amount', 10, 2)->nullable();
            $table->decimal('difference_amount', 10, 2)->default(0);

            $table->enum('status', [
                'open',
                'closed',
            ])->default('open');

            $table->foreignId('opened_by')
                ->constrained('users');

            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['barber_shop_id', 'date'], 'cash_register_shop_date_unique');
            $table->index(['barber_shop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
