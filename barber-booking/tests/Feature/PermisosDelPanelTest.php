<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\Service;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Las políticas de Shield preguntan por permisos con nombre ("Create:CashRegister").
 * Si esos permisos no existen, Gate niega todo y Filament ESCONDE los botones sin
 * mostrar ningún error: el panel parece funcionar, las tablas cargan, y no hay
 * forma de crear nada.
 *
 * Pasó en producción — el dueño veía la lista de cajas y no podía abrir una.
 */
class PermisosDelPanelTest extends TestCase
{
    use RefreshDatabase;

    private function dueño(): User
    {
        $dueño = User::factory()->create(['is_active' => true]);
        app(TenantProvisioningService::class)->provision($dueño, 'Barbería de Prueba');
        $this->artisan('permissions:sync');

        return $dueño->fresh();
    }

    public function test_el_dueño_puede_administrar_su_barberia(): void
    {
        $this->actingAs($this->dueño());

        foreach ([CashRegister::class, Service::class, \App\Models\Reservation::class] as $modelo) {
            $this->assertTrue(Gate::allows('create', $modelo), "El dueño debe poder crear " . class_basename($modelo));
            $this->assertTrue(Gate::allows('update', new $modelo), "El dueño debe poder editar " . class_basename($modelo));
            $this->assertTrue(Gate::allows('delete', new $modelo), "El dueño debe poder borrar " . class_basename($modelo));
        }
    }

    public function test_el_barbero_no_toca_la_caja(): void
    {
        $this->dueño();

        $barbero = User::factory()->create(['is_active' => true]);
        $barbero->syncRoles('barber');
        $this->actingAs($barbero->fresh());

        $this->assertFalse(Gate::allows('create', CashRegister::class), 'Un barbero no debe abrir caja.');
        $this->assertFalse(Gate::allows('delete', new CashRegister()), 'Un barbero no debe borrar cajas.');
        $this->assertTrue(Gate::allows('view', new \App\Models\Reservation()), 'Un barbero sí debe ver su agenda.');
    }

    public function test_el_boton_de_abrir_caja_es_visible_para_el_dueño(): void
    {
        // Comprobar el permiso no alcanza: lo que se rompió fue el botón.
        $this->actingAs($this->dueño());
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));

        $pagina = new \App\Filament\Resources\CashRegisters\Pages\ListCashRegisters();
        $pagina->cacheInteractsWithHeaderActions();
        $acciones = $pagina->getCachedHeaderActions();

        $this->assertNotEmpty($acciones, 'La página de cajas debe ofrecer una acción de cabecera.');
        $this->assertTrue($acciones[0]->isVisible(), 'El botón "Abrir caja" debe verse: sin él la caja es inusable.');
    }
}
