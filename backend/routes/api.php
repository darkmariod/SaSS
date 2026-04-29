<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProfessionalController;
use App\Http\Controllers\API\ReservationController;
use App\Http\Controllers\API\PaymentController;

// Auth (público)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Webhook PayPal (público)
Route::post('/paypal/webhook', [PaymentController::class, 'paypalWebhook']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Profesionales
    Route::get('/professionals', [ProfessionalController::class, 'index']);
    Route::get('/professionals/{id}', [ProfessionalController::class, 'show']);
    Route::get('/professionals/{id}/availability', [ProfessionalController::class, 'availability']);

    // Reservas
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/my-reservations', [ReservationController::class, 'myReservations']);
    Route::put('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);

    // Pagos
    Route::post('/payments/create', [PaymentController::class, 'create']);
    Route::get('/my-payments', [PaymentController::class, 'myPayments']);
});

// Rutas de éxito/cancelación PayPal (públicas)
Route::get('/paypal/success', function() {
    return response()->json(['message' => 'Pago exitoso']);
});
Route::get('/paypal/cancel', function() {
    return response()->json(['message' => 'Pago cancelado']);
});
