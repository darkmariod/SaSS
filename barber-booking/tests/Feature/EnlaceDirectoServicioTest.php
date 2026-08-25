<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El enlace que la barbería comparte en Instagram debe poder llevar a un corte
 * concreto, no solo a la lista. Es el patrón de "link in bio": el cliente toca
 * y ya tiene el servicio elegido.
 */
class EnlaceDirectoServicioTest extends TestCase
{
    use RefreshDatabase;

    private function barberia()
    {
        $dueño = User::factory()->create(['is_active' => true]);

        return app(TenantProvisioningService::class)->provision($dueño, 'Barbería de Prueba');
    }

    public function test_el_enlace_puede_traer_un_servicio_ya_elegido(): void
    {
        $barberia = $this->barberia();
        $servicio = $barberia->services()->where('service_type', 'option')->firstOrFail();

        $this->get("/barberia/{$barberia->slug}/reservar?s={$servicio->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('preselectedService.id', $servicio->id));
    }

    public function test_el_enlace_puede_traer_un_barbero_ya_elegido(): void
    {
        $barberia = $this->barberia();
        $barbero = $barberia->barbers()->firstOrFail();

        $this->get("/barberia/{$barberia->slug}/reservar?b={$barbero->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('preselectedBarber.id', $barbero->id));
    }

    public function test_un_servicio_inexistente_no_rompe_la_pagina(): void
    {
        // Un enlace viejo o mal copiado tiene que abrir igual, sin nada elegido.
        $barberia = $this->barberia();

        $this->get("/barberia/{$barberia->slug}/reservar?s=999999")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('preselectedService', null));
    }
}
