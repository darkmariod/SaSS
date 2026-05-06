<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barber_profiles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barber_shop_id')
                ->constrained('barber_shops')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('display_name');
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['barber_shop_id', 'user_id']);
            $table->index(['barber_shop_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_profiles');
    }
};
