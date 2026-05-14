<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Evento;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
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

    public function test_dashboard_returns_metrics(): void
    {
        Cotizacion::factory()->count(2)->for($this->cliente)->create();
        Evento::factory()->count(3)->for($this->cliente)->create();
        $cotizacion = Cotizacion::factory()->for($this->cliente)->create(['total' => 1000]);

        Pago::factory()->for($cotizacion)->create([
            'amount' => 500,
            'status' => 'pagado',
        ]);
        Pago::factory()->for($cotizacion)->create([
            'amount' => 500,
            'status' => 'pendiente',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'metrics',
            'income' => ['paid', 'pending'],
            'recent_quotes',
            'upcoming_events',
        ]);
    }

    public function test_dashboard_returns_zero_metrics_when_empty(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/dashboard');

        $response->assertStatus(200);

        // Clientes count includes setUp's factory, so it's 1
        $this->assertCount(4, $response->json('metrics'));
        $this->assertEquals(0, $response->json('metrics.1.value')); // Propuestas: 0 (no cotizaciones created for this test)
        $this->assertEquals(0.0, $response->json('income.paid'));
        $this->assertCount(0, $response->json('recent_quotes'));
        $this->assertCount(0, $response->json('upcoming_events'));
    }
}
