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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nombres')->after('id');
            $table->string('apellidos')->after('nombres');
            $table->string('telefono')->nullable()->after('password');
            $table->string('foto')->nullable()->after('telefono');
            $table->integer('rol_id')->default(1)->after('foto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombres', 'apellidos', 'telefono', 'foto', 'rol_id']);
        });
    }
};
