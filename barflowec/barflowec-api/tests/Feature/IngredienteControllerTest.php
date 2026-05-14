<?php

namespace Tests\Feature;

use App\Models\Ingrediente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredienteControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_all_ingredientes(): void
    {
        Ingrediente::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/ingredientes');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_store_creates_ingrediente(): void
    {
        $data = [
            'name' => 'Vodka Premium',
            'unit' => 'litro',
            'stock' => 10,
            'min_stock' => 2,
            'cost' => 25.50,
            'status' => 'activo',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/ingredientes', $data);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Vodka Premium');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/ingredientes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_show_returns_ingrediente(): void
    {
        $ingrediente = Ingrediente::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/ingredientes/{$ingrediente->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $ingrediente->id);
    }

    public function test_update_modifies_ingrediente(): void
    {
        $ingrediente = Ingrediente::factory()->create(['stock' => 5]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/ingredientes/{$ingrediente->id}", ['stock' => 20]);

        $response->assertStatus(200)
            ->assertJsonPath('stock', 20);
    }

    public function test_destroy_deletes_ingrediente(): void
    {
        $ingrediente = Ingrediente::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/ingredientes/{$ingrediente->id}");

        $response->assertStatus(200);
    }
}
