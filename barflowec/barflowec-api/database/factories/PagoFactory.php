<?php

namespace Database\Factories;

use App\Models\Cotizacion;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition(): array
    {
        return [
            'cotizacion_id' => Cotizacion::factory(),
            'amount' => fake()->randomFloat(2, 100, 3000),
            'payment_method' => fake()->randomElement(['efectivo', 'transferencia', 'tarjeta', 'cheque']),
            'paid_at' => fake()->optional(0.7)->dateTimeBetween('-1 month')?->format('Y-m-d'),
            'reference' => fake()->optional()->bothify('REF-####'),
            'status' => fake()->randomElement(['pendiente', 'pagado', 'anulado']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
