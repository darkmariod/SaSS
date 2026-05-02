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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consultant_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_per_hour', 8, 2);
            $table->integer('duration_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('consultant_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            $table->index(['consultant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
