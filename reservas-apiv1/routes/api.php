<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ConsultantController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\PaypalController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/paypal/webhook', [PayPalController::class, 'webhook']);

Route::get('/paypal/success', [PayPalController::class, 'success']);
Route::get('/paypal/cancel', function () {
    return response()->json(['message' => 'Pago cancelado']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/consultants', [ConsultantController::class, 'index']);
    Route::get('/consultants/{id}/calendar', [ConsultantController::class, 'calendar']);
    Route::get('/consultant/reservations', [ConsultantController::class, 'myReservations']);
    Route::get('/consultants/{id}', [ConsultantController::class, 'show']);
    Route::get('/consultant/calendar', [ConsultantController::class, 'myCalendar']);

    Route::get('/my-reservations', [ClientController::class, 'myReservations']);
    Route::get('/my-payments', [ClientController::class, 'myPayments']);
    Route::get('/my-profile', [ClientController::class, 'myProfile']);
    Route::put('/my-profile', [ClientController::class, 'updateProfile']);
    Route::put('/change-password', [ClientController::class, 'changePassword']);
    Route::delete('/reservations/{id}', [ClientController::class, 'cancelReservation']);

    Route::post('/paypal/create-order', [PayPalController::class, 'createOrder']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
