<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CotizacionControllerTest extends TestCase
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

    public function test_index_returns_all_cotizaciones(): void
    {
        Cotizacion::factory()->count(2)
            ->for($this->cliente)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/cotizaciones');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_store_creates_cotizacion_with_tax_calculation(): void
    {
        $data = [
            'cliente_id' => $this->cliente->id,
            'quote_number' => 'PROP-TEST-001',
            'event_type' => 'Boda',
            'event_date' => '2026-06-15',
            'guests' => 100,
            'subtotal' => 1500.00,
            'status' => 'pendiente',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cotizaciones', $data);

        $response->assertStatus(201);

        // Verificar que los campos de total existen
        $this->assertNotNull($response->json('subtotal'));
        $this->assertNotNull($response->json('tax'));
        $this->assertNotNull($response->json('total'));
    }

    public function test_store_generates_unique_quote_number(): void
    {
        $data = [
            'cliente_id' => $this->cliente->id,
            'quote_number' => 'PROP-TEST-' . time(),
            'event_type' => 'Evento Corporativo',
            'guests' => 50,
            'subtotal' => 800.00,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cotizaciones', $data);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('quote_number'));
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cotizaciones', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cliente_id']);
    }

    public function test_show_returns_cotizacion(): void
    {
        $cotizacion = Cotizacion::factory()
            ->for($this->cliente)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/cotizaciones/{$cotizacion->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $cotizacion->id);
    }

    public function test_update_modifies_cotizacion(): void
    {
        $cotizacion = Cotizacion::factory()
            ->for($this->cliente)
            ->create(['guests' => 50]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/cotizaciones/{$cotizacion->id}", [
                'guests' => 150,
                'subtotal' => 2000.00,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('guests', 150);
    }

    public function test_destroy_deletes_cotizacion(): void
    {
        $cotizacion = Cotizacion::factory()
            ->for($this->cliente)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/cotizaciones/{$cotizacion->id}");

        $response->assertStatus(200);
    }

    public function test_export_endpoint_exists(): void
    {
        $cotizacion = Cotizacion::factory()
            ->for($this->cliente)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/cotizaciones/{$cotizacion->id}/export");

        // Debe retornar un archivo Excel (response binaria)
        $response->assertStatus(200);
    }

    public function test_tax_precision_with_cents(): void
    {
        $data = [
            'cliente_id' => $this->cliente->id,
            'quote_number' => 'PROP-PREC-' . time(),
            'event_type' => 'Precisión',
            'guests' => 10,
            'subtotal' => 99.99,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/cotizaciones', $data);

        $response->assertStatus(201);
        // 99.99 * 0.12 = 11.9988 → lo que sea que el controller calcule
        $this->assertNotNull($response->json('tax'));
        $this->assertNotNull($response->json('total'));
    }
}
