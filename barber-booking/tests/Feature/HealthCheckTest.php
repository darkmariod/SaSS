<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El endpoint de salud existe para detectar diferencias de entorno que ningún
 * test de código puede ver: en el VPS, la consola corría PHP 8.4 con `intl` y
 * la web PHP 8.3 sin ella, así que `php -m` decía que todo estaba bien mientras
 * el panel devolvía 500.
 */
class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporta_el_entorno_del_proceso_que_atiende_la_web(): void
    {
        $respuesta = $this->get('/health');

        $respuesta->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('extensiones.faltantes', [])
            ->assertJsonStructure([
                'status',
                'php' => ['version', 'sapi'],
                'extensiones' => ['requeridas', 'faltantes'],
                'base_datos' => ['ok', 'escritura', 'detalle'],
            ]);
    }

    public function test_exige_intl_porque_sin_ella_el_panel_se_rompe(): void
    {
        $requeridas = $this->get('/health')->json('extensiones.requeridas');

        $this->assertContains(
            'intl',
            $requeridas,
            'intl debe seguir siendo obligatoria: las tablas de Filament formatean moneda con ella.',
        );
    }

    public function test_verifica_que_la_base_de_datos_acepte_escrituras(): void
    {
        // Leer no alcanza: el archivo SQLite llegó a quedar como sólo lectura
        // para el usuario web y las reservas fallaban con la agenda visible.
        $this->get('/health')
            ->assertOk()
            ->assertJsonPath('base_datos.escritura', true);
    }
}
