<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barber_shops', function (Blueprint $table) {
            $table->string('instagram')->nullable()->after('cover_image');
            $table->string('tiktok')->nullable()->after('instagram');
            $table->string('facebook')->nullable()->after('tiktok');
            $table->string('whatsapp')->nullable()->after('facebook');
        });
    }

    public function down(): void
    {
        Schema::table('barber_shops', function (Blueprint $table) {
            $table->dropColumn(['instagram', 'tiktok', 'facebook', 'whatsapp']);
        });
    }
};
