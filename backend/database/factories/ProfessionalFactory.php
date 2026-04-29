<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProfessionalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null, // Se asigna al crear
            'name' => fake()->name(),
            'bio' => fake()->paragraph(),
            'photo' => null,
            'is_active' => true,
        ];
    }
}
