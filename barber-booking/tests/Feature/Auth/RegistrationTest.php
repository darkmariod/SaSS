<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // El alta es de autoservicio: quien se registra crea su barbería en el
        // mismo paso, por eso `shop_name` es obligatorio y el destino ya no es
        // el dashboard sino la elección de plan.
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'shop_name' => 'Barbería de Prueba',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('register.plan', absolute: false));
    }

    public function test_registering_requires_a_shop_name(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('shop_name');
        $this->assertGuest();
    }

    public function test_registering_leaves_the_owner_with_a_usable_shop(): void
    {
        // Sin esto, alguien podría "registrarse" y quedar sin barbería, sin
        // catálogo y sin suscripción, es decir con un panel inservible.
        $this->post('/register', [
            'name' => 'Dueño Nuevo',
            'email' => 'dueno.nuevo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'shop_name' => 'Barbería Recién Creada',
        ]);

        $usuario = \App\Models\User::where('email', 'dueno.nuevo@example.com')->firstOrFail();
        $barberia = $usuario->barberShop;

        $this->assertNotNull($barberia, 'El registro debe crear la barbería del dueño.');
        $this->assertTrue($usuario->hasRole('owner'), 'El dueño debe quedar con el rol owner.');
        $this->assertNotEmpty($barberia->services, 'La barbería debe quedar con servicios para poder reservar.');
        $this->assertNotNull($barberia->subscription, 'La barbería debe arrancar con una suscripción de prueba.');
    }
}
