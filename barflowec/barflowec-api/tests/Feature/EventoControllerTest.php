<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->cliente = Cliente::factory()->create();
    }

    public function test_index_returns_all_eventos(): void
    {
        Evento::factory()->count(3)
            ->for($this->cliente)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/eventos');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_store_creates_evento(): void
    {
        $data = [
            'cliente_id' => $this->cliente->id,
            'name' => 'Boda María',
            'event_date' => '2026-12-15',
            'location' => 'Hacienda La Esperanza',
            'bartender_name' => 'Carlos Sánchez',
            'status' => 'programado',
            'notes' => 'Evento de prueba',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/eventos', $data);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Boda María');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/eventos', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cliente_id', 'name', 'event_date']);
    }

    public function test_show_returns_evento(): void
    {
        $evento = Evento::factory()
            ->for($this->cliente)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/eventos/{$evento->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $evento->id);
    }

    public function test_update_modifies_evento(): void
    {
        $evento = Evento::factory()
            ->for($this->cliente)
            ->create(['status' => 'programado']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/eventos/{$evento->id}", [
                'status' => 'completado',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'completado');
    }

    public function test_destroy_deletes_evento(): void
    {
        $evento = Evento::factory()
            ->for($this->cliente)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/eventos/{$evento->id}");

        $response->assertStatus(200);
    }
}
