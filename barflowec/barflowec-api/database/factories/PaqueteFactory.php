<?php

namespace Database\Factories;

use App\Models\Paquete;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaqueteFactory extends Factory
{
    protected $model = Paquete::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Boda Premium', 'Evento Corporativo', 'Cumpleaños Social',
                'Recepción Elegante', 'Lanzamiento Producto', 'Cena Empresarial',
            ]) . ' ' . fake()->numberBetween(30, 200) . ' invitados',
            'description' => fake()->sentence(),
            'guests_min' => fake()->numberBetween(20, 50),
            'guests_max' => fn (array $attrs) => $attrs['guests_min'] + fake()->numberBetween(30, 100),
            'price' => fake()->randomFloat(2, 800, 5000),
            'status' => fake()->randomElement(['activo', 'inactivo']),
        ];
    }
}
