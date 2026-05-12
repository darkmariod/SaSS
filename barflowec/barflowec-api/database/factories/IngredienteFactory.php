<?php

namespace Database\Factories;

use App\Models\Ingrediente;
use Illuminate\Database\Eloquent\Factories\Factory;

class IngredienteFactory extends Factory
{
    protected $model = Ingrediente::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Vodka Premium', 'Whisky 12 años', 'Ron Añejo', 'Gin London Dry',
                'Vermut Rojo', 'Jugo Natural', 'Hielo', 'Vasos cocteleros',
                'Servilletas', 'Pajillas biodegradables',
            ]),
            'unit' => fake()->randomElement(['unidad', 'litro', 'kg', 'caja', 'botella']),
            'stock' => fake()->numberBetween(0, 100),
            'min_stock' => fake()->numberBetween(5, 20),
            'cost' => fake()->randomFloat(2, 1, 200),
            'status' => fake()->randomElement(['activo', 'inactivo']),
        ];
    }
}
