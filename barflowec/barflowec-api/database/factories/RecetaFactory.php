<?php

namespace Database\Factories;

use App\Models\Receta;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecetaFactory extends Factory
{
    protected $model = Receta::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Coctelería Premium', 'Catering Ejecutivo', 'Barra Libre',
                'Maridaje Vinos', 'Coctelería Molecular', 'Mixología Clásica',
            ]),
            'description' => fake()->sentence(),
            'preparation' => fake()->paragraph(),
            'servings' => fake()->numberBetween(1, 100),
            'price' => fake()->randomFloat(2, 100, 2000),
            'status' => fake()->randomElement(['activa', 'inactiva']),
        ];
    }
}
