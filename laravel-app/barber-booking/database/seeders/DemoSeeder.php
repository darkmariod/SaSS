<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Service;
use App\Models\BarberShop;
use App\Models\BarberProfile;
use App\Models\BarberAvailability;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::where('name', 'owner')->firstOrFail();
        $barberRole = Role::where('name', 'barber')->firstOrFail();
        $customerRole = Role::where('name', 'customer')->firstOrFail();

        $owner = User::updateOrCreate(
            ['email' => 'owner@test.com'],
            [
                'role_id' => $ownerRole->id,
                'name' => 'Dueño Barbería',
                'phone' => '0999999999',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $barberUser = User::updateOrCreate(
            ['email' => 'barber@test.com'],
            [
                'role_id' => $barberRole->id,
                'name' => 'Jean Barbero',
                'phone' => '0988888888',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'cliente@test.com'],
            [
                'role_id' => $customerRole->id,
                'name' => 'Cliente Demo',
                'phone' => '0977777777',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        $shop = BarberShop::updateOrCreate(
            ['slug' => 'barberia-demo'],
            [
                'owner_id' => $owner->id,
                'name' => 'Barbería Demo',
                'phone' => '0999999999',
                'address' => 'Centro de Riobamba',
                'city' => 'Riobamba',
                'description' => 'Reserva tu corte online con nuestros barberos.',
                'bank_name' => 'Banco Pichincha',
                'bank_account' => '0000000000',
                'bank_account_owner' => 'Barbería Demo',
                'is_active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Servicios estilo Square
        |--------------------------------------------------------------------------
        |
        | main   = servicio principal visual
        | option = opción reservable dentro del servicio principal
        | addon  = extra opcional
        |
        */

        $mensCut = Service::updateOrCreate(
            [
                'barber_shop_id' => $shop->id,
                'name' => 'Mens Cut',
            ],
            [
                'category' => 'Men',
                'description' => 'Servicio principal de corte masculino.',
                'service_type' => 'main',
                'parent_service_id' => null,
                'duration_minutes' => 15,
                'price' => 0.00,
                'requires_payment' => false,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Service::updateOrCreate(
            [
                'barber_shop_id' => $shop->id,
                'name' => 'Fade',
            ],
            [
                'category' => 'Men',
                'description' => 'Corte fade profesional.',
                'service_type' => 'option',
                'parent_service_id' => $mensCut->id,
                'duration_minutes' => 45,
                'price' => 5.00,
                'requires_payment' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Service::updateOrCreate(
            [
                'barber_shop_id' => $shop->id,
                'name' => 'Fade & Beard',
            ],
            [
                'category' => 'Men',
                'description' => 'Corte fade más arreglo de barba.',
                'service_type' => 'option',
                'parent_service_id' => $mensCut->id,
                'duration_minutes' => 70,
                'price' => 8.00,
                'requires_payment' => true,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        Service::updateOrCreate(
            [
                'barber_shop_id' => $shop->id,
                'name' => 'Scissor Cut',
            ],
            [
                'category' => 'Men',
                'description' => 'Corte con tijera y acabado clásico.',
                'service_type' => 'option',
                'parent_service_id' => $mensCut->id,
                'duration_minutes' => 40,
                'price' => 6.00,
                'requires_payment' => true,
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        Service::updateOrCreate(
            [
                'barber_shop_id' => $shop->id,
                'name' => 'Beard Only',
            ],
            [
                'category' => 'Men',
                'description' => 'Arreglo y perfilado de barba.',
                'service_type' => 'option',
                'parent_service_id' => $mensCut->id,
                'duration_minutes' => 30,
                'price' => 4.00,
                'requires_payment' => true,
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        Service::updateOrCreate(
            [
                'barber_shop_id' => $shop->id,
                'name' => 'Cejas',
            ],
            [
                'category' => 'Extras',
                'description' => 'Limpieza y perfilado de cejas.',
                'service_type' => 'addon',
                'parent_service_id' => null,
                'duration_minutes' => 15,
                'price' => 2.00,
                'requires_payment' => false,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Service::updateOrCreate(
            [
                'barber_shop_id' => $shop->id,
                'name' => 'Tint Enhancement Add On',
            ],
            [
                'category' => 'Extras',
                'description' => 'Retoque visual para barba o líneas.',
                'service_type' => 'addon',
                'parent_service_id' => null,
                'duration_minutes' => 30,
                'price' => 3.00,
                'requires_payment' => false,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $barberProfile = BarberProfile::updateOrCreate(
            [
                'barber_shop_id' => $shop->id,
                'user_id' => $barberUser->id,
            ],
            [
                'display_name' => 'Jean Barber',
                'bio' => 'Especialista en cortes clásicos, barba y estilo urbano.',
                'is_active' => true,
            ]
        );

        foreach ([1, 2, 3, 4, 5, 6] as $day) {
            BarberAvailability::updateOrCreate(
                [
                    'barber_profile_id' => $barberProfile->id,
                    'day_of_week' => $day,
                ],
                [
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                    'is_active' => true,
                ]
            );
        }
    }
}
