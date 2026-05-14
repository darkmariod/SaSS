<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    protected $model = Evento::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'name' => fake()->randomElement([
                'Boda ' . fake()->lastName(),
                'Evento Corporativo',
                'Cumpleaños ' . fake()->firstName(),
                'Lanzamiento de Producto',
            ]),
            'event_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
            'location' => fake()->city() . ', ' . fake()->state(),
            'bartender_name' => fake()->name(),
            'status' => fake()->randomElement(['programado', 'confirmado', 'en_proceso', 'completado']),
            'notes' => fake()->sentence(),
        ];
    }
}
