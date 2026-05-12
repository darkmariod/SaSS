<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'company' => fake()->company(),
            'identification' => fake()->unique()->numerify('##########'),
            'address' => fake()->address(),
            'notes' => fake()->sentence(),
            'status' => 'activo',
        ];
    }
}
