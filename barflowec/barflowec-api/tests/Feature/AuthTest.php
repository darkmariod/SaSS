<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@barflowec.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@barflowec.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user'])
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nadie@test.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonPath('user.id', $user->id);
    }

    public function test_unauthenticated_user_cannot_access_me(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_logout_clears_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Sesión cerrada correctamente.');
    }

    public function test_protected_routes_require_auth(): void
    {
        $protectedRoutes = [
            '/api/me' => 'get',
            '/api/logout' => 'post',
            '/api/dashboard' => 'get',
            '/api/clientes' => 'get',
            '/api/cotizaciones' => 'get',
            '/api/eventos' => 'get',
            '/api/pagos' => 'get',
            '/api/ingredientes' => 'get',
            '/api/recetas' => 'get',
            '/api/paquetes' => 'get',
        ];

        foreach ($protectedRoutes as $route => $method) {
            $response = $method === 'post' ? $this->postJson($route) : $this->getJson($route);
            $response->assertStatus(401, "Route $route should require auth");
        }
    }

    public function test_health_endpoint_is_public(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ok');
    }
}
