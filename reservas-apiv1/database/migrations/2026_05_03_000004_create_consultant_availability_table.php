<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consultant_availability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultant_id');
            $table->tinyInteger('day_of_week')->comment('0=Sunday, 6=Saturday');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->foreign('consultant_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->unique(['consultant_id', 'day_of_week']);
            $table->index(['consultant_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultant_availability');
    }
};
