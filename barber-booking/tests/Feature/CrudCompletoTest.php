<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TenantProvisioningService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cada sección del panel tiene que permitir crear, editar y eliminar.
 *
 * Filament esconde en silencio las acciones que no puede autorizar o que nadie
 * declaró: el panel se ve completo y sano mientras el dueño no tiene forma de
 * dar de alta nada. Ya pasó con la caja.
 */
class CrudCompletoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function recursos(): array
    {
        return [
            'Cajas' => [\App\Filament\Resources\CashRegisters\CashRegisterResource::class],
            'Servicios' => [\App\Filament\Resources\Services\ServiceResource::class],
            'Barberos' => [\App\Filament\Resources\BarberProfiles\BarberProfileResource::class],
            'Horarios' => [\App\Filament\Resources\BarberAvailabilities\BarberAvailabilityResource::class],
            'Movimientos' => [\App\Filament\Resources\CashMovements\CashMovementResource::class],
            'Pagos a barberos' => [\App\Filament\Resources\BarberPayments\BarberPaymentResource::class],
            'Reservas' => [\App\Filament\Resources\Reservations\ReservationResource::class],
            'Transferencias' => [\App\Filament\Resources\Transfers\TransferResource::class],
        ];
    }

    private function entrarComoDueño(): void
    {
        $dueño = User::factory()->create(['is_active' => true]);
        app(TenantProvisioningService::class)->provision($dueño, 'Barbería de Prueba');
        $this->artisan('permissions:sync');

        $this->actingAs($dueño->fresh());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('recursos')]
    public function test_el_dueño_puede_crear_editar_y_eliminar(string $recurso): void
    {
        $this->entrarComoDueño();

        $this->assertTrue($recurso::canCreate(), "Falta permiso de creación en {$recurso}.");

        $paginas = $recurso::getPages();
        $this->assertArrayHasKey('create', $paginas, "El recurso {$recurso} no tiene página de creación.");
        $this->assertArrayHasKey('edit', $paginas, "El recurso {$recurso} no tiene página de edición.");
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('recursos')]
    public function test_cada_seccion_ofrece_borrar_una_fila(string $recurso): void
    {
        // El borrado masivo no alcanza: obliga a marcar casillas para sacar un
        // solo registro, y es la operación que más se usa al corregir un error.
        $archivo = (new \ReflectionClass($recurso))->getFileName();

        $this->assertStringContainsString(
            'DeleteAction::make',
            file_get_contents($archivo),
            "El recurso {$recurso} no ofrece eliminar una fila individual.",
        );
    }
}
