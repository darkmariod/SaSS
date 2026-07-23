<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // setup_price = pago único de configuración (primer mes $20). annual_price =
        // prepago anual (mensual x 10 = 2 meses gratis).
        Plan::updateOrCreate(
            ['slug' => 'basico'],
            [
                'name' => 'Básico',
                'description' => 'Perfecto para barberías que están empezando. Hasta 3 barberos.',
                'max_barbers' => 3,
                'setup_price' => 20.00,
                'monthly_price' => 10.00,
                'annual_price' => 100.00,
                'trial_days' => 30,
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'profesional'],
            [
                'name' => 'Profesional',
                'description' => 'Para barberías en crecimiento. Hasta 5 barberos.',
                'max_barbers' => 5,
                'setup_price' => 20.00,
                'monthly_price' => 10.00,
                'annual_price' => 100.00,
                'trial_days' => 30,
                'is_active' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'premium'],
            [
                'name' => 'Premium',
                'description' => 'Para barberías establecidas. Hasta 10 barberos.',
                'max_barbers' => 10,
                'setup_price' => 20.00,
                'monthly_price' => 15.00,
                'annual_price' => 150.00,
                'trial_days' => 30,
                'is_active' => true,
            ]
        );
    }
}
