<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        // GET / redirects unauthenticated users to the public booking page
        $response = $this->get('/');

        $response->assertStatus(302);
    }

    public function test_health_endpoint_works(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'app' => 'BarberBooking EC',
            ]);
    }
}
