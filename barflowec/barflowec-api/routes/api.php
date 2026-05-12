<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CotizacionController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\IngredienteController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\PaqueteController;
use App\Http\Controllers\Api\RecetaController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'BarFlowEC API',
    ]);
});

// Login con rate limiting (5 intentos/minuto por IP)
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');

// Rutas protegidas con rate limiting general (120 req/min)
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/cotizaciones/{cotizacion}/export', [CotizacionController::class, 'export']);

    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('ingredientes', IngredienteController::class);
    Route::apiResource('recetas', RecetaController::class);
    Route::apiResource('paquetes', PaqueteController::class);
    Route::apiResource('cotizaciones', CotizacionController::class);
    Route::apiResource('eventos', EventoController::class);
    Route::apiResource('pagos', PagoController::class);
});
