<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cash_register_id')
                ->constrained('cash_registers')
                ->cascadeOnDelete();

            $table->enum('type', [
                'income',
                'expense',
            ]);

            $table->decimal('amount', 10, 2);

            $table->string('description');

            $table->foreignId('performed_by')
                ->constrained('users');

            $table->timestamps();

            $table->index(['cash_register_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
