<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicBookingController;

// Rutas públicas para reservas (API/JSON)
Route::post('/barberia/availability/check', [PublicBookingController::class, 'availability'])
    ->name('public.booking.availability');

Route::post('/barberia/reservations', [PublicBookingController::class, 'store'])
    ->name('public.booking.store');

Route::post('/barberia/reservations/{reservation}/receipt', [PublicBookingController::class, 'uploadReceipt'])
    ->name('public.booking.receipt');
