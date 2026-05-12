<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Cotizacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class CotizacionFactory extends Factory
{
    protected $model = Cotizacion::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 5000);
        $tax = round($subtotal * 0.12, 2);

        return [
            'cliente_id' => Cliente::factory(),
            'quote_number' => 'PROP-' . fake()->unique()->numerify('####'),
            'event_type' => fake()->randomElement(['Boda', 'Corporativo', 'Cumpleaños', 'Social']),
            'event_date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'guests' => fake()->numberBetween(20, 200),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => round($subtotal + $tax, 2),
            'status' => fake()->randomElement(['pendiente', 'enviada', 'aprobada', 'rechazada']),
            'notes' => fake()->sentence(),
        ];
    }
}
