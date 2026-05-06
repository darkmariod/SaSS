<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barber_shops', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->text('description')->nullable();

            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_account_owner')->nullable();
            $table->string('bank_qr_image')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['owner_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_shops');
    }
};
