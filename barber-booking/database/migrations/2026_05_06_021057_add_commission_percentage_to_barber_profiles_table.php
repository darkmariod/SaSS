<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barber_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('barber_profiles', 'commission_percentage')) {
                $table->decimal('commission_percentage', 5, 2)
                    ->default(50.00)
                    ->after('photo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barber_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('barber_profiles', 'commission_percentage')) {
                $table->dropColumn('commission_percentage');
            }
        });
    }
};
