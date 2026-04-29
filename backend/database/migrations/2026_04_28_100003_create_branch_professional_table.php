<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_professional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('professionals')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->unique(['branch_id', 'professional_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_professional');
    }
};
