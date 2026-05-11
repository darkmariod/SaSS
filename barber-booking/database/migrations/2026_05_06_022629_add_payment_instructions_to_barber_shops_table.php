<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barber_shops', function (Blueprint $table) {
            if (! Schema::hasColumn('barber_shops', 'payment_instructions')) {
                $table->text('payment_instructions')->nullable()->after('bank_account_owner');
            }
        });
    }

    public function down(): void
    {
        Schema::table('barber_shops', function (Blueprint $table) {
            if (Schema::hasColumn('barber_shops', 'payment_instructions')) {
                $table->dropColumn('payment_instructions');
            }
        });
    }
};
