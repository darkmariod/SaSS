<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Chequeo de salud del entorno, respondido por el mismo proceso que sirve la
 * aplicación.
 *
 * Esto importa más de lo que parece: en el VPS la línea de comandos corre una
 * versión de PHP distinta a la de PHP-FPM, así que `php -m` en una terminal
 * puede mostrar una extensión que la web no tiene. Un panel entero se cayó por
 * esa diferencia (faltaba `intl` sólo en FPM). Verificar por HTTP es la única
 * forma de mirar el entorno que de verdad atiende a los clientes.
 */
class HealthController extends Controller
{
    /**
     * Extensiones sin las cuales la aplicación se rompe en producción.
     * `intl` sostiene el formato de moneda de las tablas de Filament; sin ella
     * el panel devuelve 500 aunque el código esté bien.
     */
    private const EXTENSIONES_REQUERIDAS = [
        'intl',
        'mbstring',
        'openssl',
        'pdo_sqlite',
        'curl',
        'fileinfo',
        'zip',
        'bcmath',
        'xml',
    ];

    public function __invoke(): JsonResponse
    {
        $faltantes = array_values(array_filter(
            self::EXTENSIONES_REQUERIDAS,
            fn (string $ext): bool => ! extension_loaded($ext),
        ));

        $baseDatos = $this->revisarBaseDatos();

        $sano = $faltantes === [] && $baseDatos['ok'];

        return response()->json([
            'status' => $sano ? 'ok' : 'degradado',
            'app' => config('app.name'),
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
            ],
            'extensiones' => [
                'requeridas' => self::EXTENSIONES_REQUERIDAS,
                'faltantes' => $faltantes,
            ],
            'base_datos' => $baseDatos,
        ], $sano ? 200 : 503);
    }

    /**
     * @return array{ok: bool, escritura: bool, detalle: string}
     */
    private function revisarBaseDatos(): array
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return ['ok' => false, 'escritura' => false, 'detalle' => 'sin conexión'];
        }

        // Leer no alcanza: en este despliegue el archivo SQLite llegó a quedar
        // como sólo lectura para el usuario web, y las reservas fallaban aunque
        // la agenda se viera perfecta.
        try {
            DB::statement('CREATE TABLE IF NOT EXISTS _health_probe (id INTEGER)');
            DB::statement('DROP TABLE _health_probe');
        } catch (\Throwable $e) {
            return ['ok' => false, 'escritura' => false, 'detalle' => 'sin permiso de escritura'];
        }

        return ['ok' => true, 'escritura' => true, 'detalle' => 'conecta y escribe'];
    }
}
