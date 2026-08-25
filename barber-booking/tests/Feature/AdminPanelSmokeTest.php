<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El panel se cayó en producción y las páginas seguían devolviendo 200 con el
 * error dibujado adentro, así que nadie se enteró hasta abrirlo a mano. Estas
 * pruebas recorren cada pantalla del panel y fallan tanto por código de estado
 * como por texto de error incrustado.
 */
class AdminPanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Todas las secciones del panel a las que llega el dueño de una barbería.
     *
     * @return array<int, array{0: string}>
     */
    public static function rutasDelPanel(): array
    {
        return [
            ['/admin'],
            ['/admin/profile'],
            ['/admin/barber-shops'],
            ['/admin/reservations'],
            ['/admin/services'],
            ['/admin/barber-profiles'],
            ['/admin/transfers'],
            ['/admin/cash-registers'],
            ['/admin/barber-payments'],
        ];
    }

    private function dueñoConBarberia(): User
    {
        $dueño = User::factory()->create(['is_active' => true]);

        app(TenantProvisioningService::class)->provision($dueño, 'Barbería de Prueba');

        return $dueño->fresh();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('rutasDelPanel')]
    public function test_el_dueño_abre_cada_seccion_del_panel(string $ruta): void
    {
        $respuesta = $this->actingAs($this->dueñoConBarberia())->get($ruta);

        $respuesta->assertOk();

        // Filament dibuja el fallo dentro de la página y devuelve 200 igual:
        // sin esta comprobación, un panel roto pasaría por sano.
        $this->assertStringNotContainsString(
            'PHP extension is required',
            $respuesta->getContent(),
            "La página $ruta cargó pero contiene un error de extensión de PHP.",
        );
    }

    public function test_el_barbero_puede_entrar_y_cambiar_su_contraseña(): void
    {
        $dueño = $this->dueñoConBarberia();

        $barbero = User::factory()->create(['is_active' => true]);
        $barbero->syncRoles('barber');

        $this->actingAs($barbero)->get('/admin')->assertOk();

        $perfil = $this->actingAs($barbero)->get('/admin/profile');
        $perfil->assertOk();
        $this->assertStringContainsString('password', $perfil->getContent());
    }

    public function test_un_dueño_sin_suscripcion_no_entra_al_panel(): void
    {
        $dueño = $this->dueñoConBarberia();

        // Sin suscripción activa el middleware manda a elegir plan. Esto quedó
        // como prueba porque la barbería de demostración se quedó sin registro
        // de suscripción y el dueño no podía abrir su propio panel.
        $dueño->barberShop->subscriptions()->delete();

        $this->actingAs($dueño->fresh())
            ->get('/admin')
            ->assertRedirect(route('register.plan'));
    }
}
