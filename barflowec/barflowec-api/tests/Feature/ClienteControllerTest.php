<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_all_clientes(): void
    {
        Cliente::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/clientes');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_store_creates_cliente(): void
    {
        $data = [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'phone' => '0999999999',
            'company' => 'Empresa SAC',
            'identification' => '1712345678',
            'address' => 'Av. Siempre Viva 123',
            'notes' => 'Cliente frecuente',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/clientes', $data);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Juan Pérez')
            ->assertJsonPath('email', 'juan@example.com');

        $this->assertDatabaseHas('clientes', ['email' => 'juan@example.com']);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/clientes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_show_returns_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/clientes/{$cliente->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $cliente->id);
    }

    public function test_show_returns_404_for_nonexistent(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/clientes/9999');

        $response->assertStatus(404);
    }

    public function test_update_modifies_cliente(): void
    {
        $cliente = Cliente::factory()->create(['name' => 'Original']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/clientes/{$cliente->id}", ['name' => 'Actualizado']);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Actualizado');

        $this->assertDatabaseHas('clientes', ['name' => 'Actualizado']);
    }

    public function test_destroy_deletes_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/clientes/{$cliente->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Cliente eliminado.');
        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }

    public function test_index_returns_empty_when_no_clientes(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/clientes');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
