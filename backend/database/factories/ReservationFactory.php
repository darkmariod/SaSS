<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $date = Carbon::now()->addDays(fake()->numberBetween(1, 30));
        $startHour = fake()->numberBetween(8, 16);
        $startTime = Carbon::createFromTime($startHour, 0, 0);
        $endTime = $startTime->copy()->addHour();

        return [
            'user_id' => null, // Se asigna al crear
            'branch_id' => null,
            'professional_id' => null,
            'reservation_date' => $date->format('Y-m-d'),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'reservation_status' => 'confirmed',
            'payment_status' => 'pending',
            'total_amount' => fake()->randomFloat(2, 50, 500),
            'cancellation_reason' => null,
        ];
    }
}
