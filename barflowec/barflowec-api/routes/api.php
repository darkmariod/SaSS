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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('ingredientes', IngredienteController::class);
    Route::apiResource('recetas', RecetaController::class);
    Route::apiResource('paquetes', PaqueteController::class);
    Route::apiResource('cotizaciones', CotizacionController::class);
    Route::apiResource('eventos', EventoController::class);
    Route::apiResource('pagos', PagoController::class);
});
