<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'owner'],
            [
                'display_name' => 'Dueño',
                'is_active' => true,
            ]
        );

        Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrador',
                'is_active' => true,
            ]
        );

        Role::firstOrCreate(
            ['name' => 'barber'],
            [
                'display_name' => 'Barbero',
                'is_active' => true,
            ]
        );

        Role::firstOrCreate(
            ['name' => 'customer'],
            [
                'display_name' => 'Cliente',
                'is_active' => true,
            ]
        );
    }
}
