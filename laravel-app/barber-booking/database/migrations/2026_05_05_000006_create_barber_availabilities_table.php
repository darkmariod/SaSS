<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barber_availabilities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('barber_profile_id')
                ->constrained('barber_profiles')
                ->cascadeOnDelete();

            $table->tinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(
                ['barber_profile_id', 'day_of_week', 'is_active'],
                'barber_availability_lookup_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_availabilities');
    }
};
