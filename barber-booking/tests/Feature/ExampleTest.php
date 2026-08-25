<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_raiz_muestra_la_pagina_de_venta(): void
    {
        // La raíz vende el producto: quien escribe sólo la dirección es un
        // dueño de barbería evaluando, no el cliente de una barbería.
        $this->get('/')->assertOk();
    }

    public function test_demo_avisa_cuando_no_hay_ninguna_barberia(): void
    {
        $this->get('/demo')->assertNotFound();
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
