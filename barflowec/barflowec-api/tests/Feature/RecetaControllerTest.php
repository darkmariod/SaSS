<?php

namespace Tests\Feature;

use App\Models\Receta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecetaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_all_recetas(): void
    {
        Receta::factory()->count(2)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/recetas');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_store_creates_receta(): void
    {
        $data = [
            'name' => 'Coctelería Premium',
            'description' => 'Servicio completo de coctelería',
            'preparation' => 'Incluye barra libre por 4 horas',
            'servings' => 50,
            'price' => 450.00,
            'status' => 'activa',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/recetas', $data);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Coctelería Premium');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/recetas', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_show_returns_receta(): void
    {
        $receta = Receta::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/recetas/{$receta->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $receta->id);
    }

    public function test_update_modifies_receta(): void
    {
        $receta = Receta::factory()->create(['price' => 100.00]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/recetas/{$receta->id}", ['price' => 150.00]);

        $response->assertStatus(200)
            ->assertJsonPath('price', 150);
    }

    public function test_destroy_deletes_receta(): void
    {
        $receta = Receta::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/recetas/{$receta->id}");

        $response->assertStatus(200);
    }
}
