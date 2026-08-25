<?php

/**
 * Chequeo de humo contra un despliegue real.
 *
 * Se ejecuta CONTRA EL SERVIDOR POR HTTP, no dentro de la aplicación, porque
 * los fallos que tumbaron este despliegue no eran de código: una extensión de
 * PHP presente en consola pero ausente en FPM, un archivo SQLite sin permiso de
 * escritura, buffers de nginx demasiado chicos para las cabeceras de Filament.
 * Ningún test unitario los ve. Este script sí.
 *
 * Uso:
 *   php bin/smoke.php http://108.174.152.179:3000 owner@test.com dueno2026
 *
 * Devuelve 0 si todo pasa, 1 si algo falla (sirve para CI o para un hook de
 * post-despliegue).
 */

$base  = rtrim($argv[1] ?? 'http://localhost:8000', '/');
$email = $argv[2] ?? null;
$pass  = $argv[3] ?? null;

$fallos = [];
$jar    = tempnam(sys_get_temp_dir(), 'smoke');

function pedir(string $url, array $opciones, string $jar): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, $opciones + [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT        => 20,
    ]);
    $respuesta = curl_exec($ch);
    $corte     = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $codigo    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$codigo, substr((string) $respuesta, 0, $corte), substr((string) $respuesta, $corte)];
}

function titulo(string $t): void
{
    echo "\n" . $t . "\n" . str_repeat('-', strlen($t)) . "\n";
}

// ---------------------------------------------------------------- entorno ---
titulo('Entorno (visto por el proceso web)');

[$codigo, , $cuerpo] = pedir("$base/health", [], $jar);
$salud = json_decode($cuerpo, true);

if ($codigo !== 200 || ! is_array($salud)) {
    $fallos[] = "/health respondió HTTP $codigo";
    printf("  [FALLA] /health HTTP %s\n", $codigo);
    if (is_array($salud) && $salud['extensiones']['faltantes'] ?? false) {
        printf("          extensiones faltantes: %s\n", implode(', ', $salud['extensiones']['faltantes']));
    }
} else {
    printf("  [OK] PHP %s (%s)\n", $salud['php']['version'], $salud['php']['sapi']);
    printf("  [OK] extensiones requeridas presentes\n");
    printf("  [OK] base de datos: %s\n", $salud['base_datos']['detalle']);
}

// ------------------------------------------------------------- flujo web ---
titulo('Páginas públicas');

// La dirección de la barbería NO se escribe a mano: se descubre siguiendo la
// raíz del sitio. Cuando estaba fija, un cambio de nombre desde el panel la
// dejó apuntando a una página que ya no existía y este chequeo no lo notaba.
[$codigo, $cabeceras] = pedir("$base/", [], $jar);
preg_match('/^Location:\s*(.+)$/mi', $cabeceras, $m);
$publica = isset($m[1]) ? parse_url(trim($m[1]), PHP_URL_PATH) : null;

if (! $publica) {
    $fallos[] = 'la raíz del sitio no lleva a ninguna barbería';
    printf("  [FALLA] %-40s HTTP %s (sin redirección)\n", '/', $codigo);
} else {
    foreach ([$publica, $publica . '/reservar'] as $ruta) {
        [$codigo] = pedir("$base$ruta", [], $jar);
        $ok = $codigo === 200;
        $ok || $fallos[] = "$ruta devolvió $codigo";
        printf("  [%s] %-40s HTTP %s\n", $ok ? 'OK' : 'FALLA', $ruta, $codigo);
    }
}

// ----------------------------------------------------------------- panel ---
if ($email && $pass) {
    titulo("Panel autenticado ($email)");

    [, , $html] = pedir("$base/admin/login", [], $jar);
    preg_match('/name="csrf-token" content="([^"]+)"/', $html, $m);
    $token = $m[1] ?? '';
    preg_match('/wire:snapshot="([^"]+)"/', $html, $m);
    $snapshot = html_entity_decode($m[1] ?? '', ENT_QUOTES);

    if (! $token || ! $snapshot) {
        $fallos[] = 'no se pudo leer el formulario de login';
        echo "  [FALLA] no se pudo leer el formulario de login\n";
    } else {
        pedir("$base/livewire/update", [
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => json_encode([
                '_token'     => $token,
                'components' => [[
                    'snapshot' => $snapshot,
                    'updates'  => ['data.email' => $email, 'data.password' => $pass],
                    'calls'    => [['path' => '', 'method' => 'authenticate', 'params' => []]],
                ]],
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "X-CSRF-TOKEN: $token",
                'X-Livewire: true',
            ],
        ], $jar);

        $rutas = [
            '/admin',
            '/admin/profile',
            '/admin/barber-shops',
            '/admin/reservations',
            '/admin/services',
            '/admin/barber-profiles',
            '/admin/transfers',
            '/admin/cash-registers',
            '/admin/barber-payments',
        ];

        foreach ($rutas as $ruta) {
            [$codigo, , $cuerpo] = pedir("$base$ruta", [], $jar);

            // Filament devuelve 200 con el error dibujado dentro del widget, así
            // que el código de estado por sí solo no alcanza para dar por buena
            // la página.
            $conError = stripos($cuerpo, 'SERVER ERROR') !== false
                || stripos($cuerpo, 'PHP extension is required') !== false;

            $ok = $codigo === 200 && ! $conError;
            $ok || $fallos[] = "$ruta devolvió $codigo" . ($conError ? ' con error incrustado' : '');

            printf("  [%s] %-40s HTTP %s%s\n",
                $ok ? 'OK' : 'FALLA', $ruta, $codigo, $conError ? '  (error dentro de la página)' : '');
        }
    }
}

@unlink($jar);

// --------------------------------------------------------------- veredicto ---
titulo('Resultado');

if ($fallos === []) {
    echo "  TODO OK\n\n";
    exit(0);
}

echo '  ' . count($fallos) . " problema(s):\n";
foreach ($fallos as $f) {
    echo "    - $f\n";
}
echo "\n";
exit(1);
