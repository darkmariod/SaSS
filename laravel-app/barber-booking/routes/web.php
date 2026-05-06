<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PublicBookingController;

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('/health', HealthController::class);

// Barber routes (solo para barberos)
Route::middleware(['auth', 'role:barber'])->group(function () {
    Route::get('/barbero/mis-citas', [BarberController::class, 'myAppointments'])->name('barber.appointments');
    Route::patch('/barbero/reservations/{reservation}/status', [BarberController::class, 'updateStatus'])->name('barber.reservations.update-status');
});

Route::get('/barberia/{slug}', [PublicBookingController::class, 'show'])
    ->name('public.booking.show');

Route::post('/barberia/availability/check', [PublicBookingController::class, 'availability'])
    ->name('public.booking.availability');

Route::post('/barberia/reservations', [PublicBookingController::class, 'store'])
    ->name('public.booking.store');

Route::post('/barberia/reservations/{reservation}/receipt', [PublicBookingController::class, 'uploadReceipt'])
    ->name('public.booking.receipt');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
