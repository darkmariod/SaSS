<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spatie_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name'], 'spatie_permissions_name_guard_unique');
        });

        Schema::create('spatie_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name'], 'spatie_roles_name_guard_unique');
        });

        Schema::create('model_has_spatie_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type'], 'model_has_spatie_permissions_model_idx');

            $table->foreign('permission_id', 'model_has_spatie_permissions_permission_fk')
                ->references('id')
                ->on('spatie_permissions')
                ->onDelete('cascade');

            $table->primary(
                ['permission_id', 'model_id', 'model_type'],
                'model_has_spatie_permissions_primary'
            );
        });

        Schema::create('model_has_spatie_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->index(['model_id', 'model_type'], 'model_has_spatie_roles_model_idx');

            $table->foreign('role_id', 'model_has_spatie_roles_role_fk')
                ->references('id')
                ->on('spatie_roles')
                ->onDelete('cascade');

            $table->primary(
                ['role_id', 'model_id', 'model_type'],
                'model_has_spatie_roles_primary'
            );
        });

        Schema::create('spatie_role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id', 'spatie_role_has_permissions_permission_fk')
                ->references('id')
                ->on('spatie_permissions')
                ->onDelete('cascade');

            $table->foreign('role_id', 'spatie_role_has_permissions_role_fk')
                ->references('id')
                ->on('spatie_roles')
                ->onDelete('cascade');

            $table->primary(
                ['permission_id', 'role_id'],
                'spatie_role_has_permissions_primary'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spatie_role_has_permissions');
        Schema::dropIfExists('model_has_spatie_roles');
        Schema::dropIfExists('model_has_spatie_permissions');
        Schema::dropIfExists('spatie_roles');
        Schema::dropIfExists('spatie_permissions');
    }
};
