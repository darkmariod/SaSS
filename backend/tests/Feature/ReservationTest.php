<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Reservation;
use Database\Factories\BranchFactory;
use Database\Factories\ProfessionalFactory;
use Database\Factories\ServiceFactory;
use Database\Factories\ReservationFactory;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_can_create_reservation()
    {
        // Crear datos necesarios
        $user = User::factory()->create(['rol_id' => config('roles.client', 3)]);
        $branch = Branch::factory()->create();
        $professional = Professional::factory()->create();
        $service = Service::factory()->create();

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/reservations', [
                'branch_id' => $branch->id,
                'professional_id' => $professional->id,
                'service_id' => $service->id,
                'reservation_date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '10:00:00',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reservations', [
            'user_id' => $user->id,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_cannot_create_reservation_without_auth()
    {
        $branch = Branch::factory()->create();
        $professional = Professional::factory()->create();
        $service = Service::factory()->create();

        $response = $this->postJson('/api/reservations', [
            'branch_id' => $branch->id,
            'professional_id' => $professional->id,
            'service_id' => $service->id,
            'reservation_date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '10:00',
        ]);

        $response->assertStatus(401);
    }

    public function test_can_get_my_reservations()
    {
        $user = User::factory()->create(['rol_id' => config('roles.client', 3)]);
        $branch = Branch::factory()->create();
        $professional = Professional::factory()->create();

        Reservation::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'professional_id' => $professional->id,
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/my-reservations');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'reservation_date',
                        'start_time',
                    ]
                ]
            ]
        ]);
    }
}
