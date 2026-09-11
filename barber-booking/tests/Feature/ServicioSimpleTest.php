<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El dueño de una barbería carga "Fade, $5, 45 minutos". No tiene por qué
 * saber que por debajo existe un servicio contenedor al que hay que colgarlo:
 * pedirle un "servicio padre" en el formulario lo obligaba a entender el modelo
 * de datos para dar de alta un corte.
 */
class ServicioSimpleTest extends TestCase
{
    use RefreshDatabase;

    private function barberia()
    {
        $dueño = User::factory()->create(['is_active' => true]);

        return app(TenantProvisioningService::class)->provision($dueño, 'Barbería de Prueba');
    }

    public function test_un_servicio_nuevo_queda_reservable_sin_indicar_el_padre(): void
    {
        $barberia = $this->barberia();

        $servicio = Service::create([
            'barber_shop_id' => $barberia->id,
            'name' => 'Fade',
            'category' => 'Hombre',
            'service_type' => 'option',
            'duration_minutes' => 45,
            'price' => 5.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertNotNull($servicio->parent_service_id, 'El servicio debe quedar colgado de un principal.');
        $this->assertSame('main', Service::find($servicio->parent_service_id)->service_type);
    }

    public function test_el_servicio_nuevo_queda_realmente_reservable(): void
    {
        // No alcanza con buscar el nombre en el HTML de la página pública:
        // Inertia serializa todas las props ahí, así que el texto aparece
        // aunque la vista no lo dibuje y aunque el servicio no fuera
        // reservable. La prueba honesta es pedir horarios para él.
        $barberia = $this->barberia();

        $servicio = Service::create([
            'barber_shop_id' => $barberia->id,
            'name' => 'Corte Navaja',
            'category' => 'Hombre',
            'service_type' => 'option',
            'duration_minutes' => 30,
            'price' => 7.00,
            'is_active' => true,
            'sort_order' => 9,
        ]);

        $barbero = $barberia->barbers()->first();

        $respuesta = $this->postJson('/barberia/availability/check', [
            'service_id' => $servicio->id,
            'barber_profile_id' => $barbero->id,
            // Siempre el próximo lunes: el barbero recién creado atiende de lunes
            // a sábado, y "dentro de dos días" caía en domingo los viernes.
            'date' => now()->next(\Carbon\Carbon::MONDAY)->toDateString(),
        ]);

        $respuesta->assertOk();
        $this->assertNotEmpty(
            $respuesta->json('slots'),
            'Un servicio recién creado tiene que devolver horarios disponibles.',
        );
    }

    public function test_un_extra_no_se_cuelga_de_nadie(): void
    {
        $barberia = $this->barberia();

        $extra = Service::create([
            'barber_shop_id' => $barberia->id,
            'name' => 'Cejas',
            'category' => 'Extras',
            'service_type' => 'addon',
            'duration_minutes' => 10,
            'price' => 2.00,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertNull($extra->parent_service_id, 'Un extra es independiente, no cuelga de un corte.');
    }

    public function test_no_se_crea_un_principal_por_cada_servicio(): void
    {
        $barberia = $this->barberia();
        $antes = Service::where('barber_shop_id', $barberia->id)->where('service_type', 'main')->count();

        foreach (['Uno', 'Dos', 'Tres'] as $i => $nombre) {
            Service::create([
                'barber_shop_id' => $barberia->id,
                'name' => $nombre,
                'category' => 'Hombre',
                'service_type' => 'option',
                'duration_minutes' => 20,
                'price' => 5,
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        $this->assertSame(
            $antes,
            Service::where('barber_shop_id', $barberia->id)->where('service_type', 'main')->count(),
            'Todos los servicios deben reutilizar el mismo contenedor.',
        );
    }
}
