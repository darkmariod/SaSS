<?php

namespace Tests\Feature;

use App\Models\Paquete;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaqueteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_all_paquetes(): void
    {
        Paquete::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/paquetes');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_store_creates_paquete(): void
    {
        $data = [
            'name' => 'Boda Premium',
            'description' => 'Paquete completo para bodas',
            'guests_min' => 50,
            'guests_max' => 150,
            'price' => 2500.00,
            'status' => 'activo',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/paquetes', $data);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Boda Premium')
            ->assertJsonPath('price', 2500);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/paquetes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_show_returns_paquete(): void
    {
        $paquete = Paquete::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/paquetes/{$paquete->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $paquete->id);
    }

    public function test_update_modifies_paquete(): void
    {
        $paquete = Paquete::factory()->create(['price' => 1000.00]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/paquetes/{$paquete->id}", ['price' => 1200.00]);

        $response->assertStatus(200)
            ->assertJsonPath('price', 1200);
    }

    public function test_destroy_deletes_paquete(): void
    {
        $paquete = Paquete::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/paquetes/{$paquete->id}");

        $response->assertStatus(200);
    }
}
