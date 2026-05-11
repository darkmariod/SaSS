<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Custom role (role_id FK on users table)
        Role::firstOrCreate(
            ['name' => 'owner'],
            ['display_name' => 'Dueño', 'is_active' => true]
        );
        Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrador', 'is_active' => true]
        );
        Role::firstOrCreate(
            ['name' => 'barber'],
            ['display_name' => 'Barbero', 'is_active' => true]
        );
        Role::firstOrCreate(
            ['name' => 'customer'],
            ['display_name' => 'Cliente', 'is_active' => true]
        );

        // Spatie roles (used by HasRoles trait → hasRole(), syncRoles())
        SpatieRole::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        SpatieRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        SpatieRole::firstOrCreate(['name' => 'barber', 'guard_name' => 'web']);
        SpatieRole::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
    }
}
