<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barber_shop_id')
                ->constrained('barber_shops')
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->unsignedInteger('duration_minutes');
            $table->decimal('price', 10, 2);

            $table->boolean('requires_payment')->default(true);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['barber_shop_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
