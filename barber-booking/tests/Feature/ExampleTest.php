<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_raiz_avisa_cuando_no_hay_ninguna_barberia(): void
    {
        // Sin barberías publicadas la raíz no tiene a dónde llevar. Antes
        // redirigía a un slug fijo y el visitante caía en un 404 sin
        // explicación; ahora el 404 es explícito.
        $this->get('/')->assertNotFound();
    }

    public function test_health_endpoint_works(): void
    {
        $response = $this->get('/health');

        // El nombre sale de la configuración: fijarlo a mano ya rompió esta
        // prueba una vez. El contrato que importa es el estado.
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'app' => config('app.name'),
            ]);
    }
}
