<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Cliente $cliente;
    private Cotizacion $cotizacion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->cliente = Cliente::factory()->create();
        $this->cotizacion = Cotizacion::factory()
            ->for($this->cliente)
            ->create();
    }

    public function test_index_returns_all_pagos(): void
    {
        Pago::factory()->count(2)
            ->for($this->cotizacion)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/pagos');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_store_creates_pago(): void
    {
        $data = [
            'cotizacion_id' => $this->cotizacion->id,
            'amount' => 500.00,
            'payment_method' => 'transferencia',
            'paid_at' => '2026-05-10',
            'reference' => 'TRF-001',
            'status' => 'pagado',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/pagos', $data);

        $response->assertStatus(201)
            ->assertJsonPath('amount', 500)
            ->assertJsonPath('payment_method', 'transferencia');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/pagos', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    public function test_show_returns_pago(): void
    {
        $pago = Pago::factory()
            ->for($this->cotizacion)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/pagos/{$pago->id}");

        $response->assertStatus(200)
            ->assertJsonPath('id', $pago->id);
    }

    public function test_update_modifies_pago(): void
    {
        $pago = Pago::factory()
            ->for($this->cotizacion)
            ->create(['status' => 'pendiente']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/pagos/{$pago->id}", ['status' => 'pagado']);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'pagado');
    }

    public function test_destroy_deletes_pago(): void
    {
        $pago = Pago::factory()
            ->for($this->cotizacion)
            ->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/pagos/{$pago->id}");

        $response->assertStatus(200);
    }

    public function test_all_payment_methods_are_accepted(): void
    {
        $methods = ['efectivo', 'transferencia', 'tarjeta', 'cheque'];

        foreach ($methods as $method) {
            $data = [
                'cotizacion_id' => $this->cotizacion->id,
                'amount' => 100.00,
                'payment_method' => $method,
                'status' => 'pagado',
            ];

            $response = $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/pagos', $data);

            $response->assertStatus(201);
        }
    }
}
