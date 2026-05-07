<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\BarberController;

Route::get('/', function () {
    return inertia::render('Home');
});

Route::get('/health', HealthController::class);

// Barber routes (solo para barberos)
Route::middleware(['auth', 'role:barber'])->group(function () {
    Route::get('/barbero/mis-citas', [BarberController::class, 'myAppointments'])->name('barber.appointments');
    Route::patch('/barbero/reservations/{reservation}/status', [BarberController::class, 'updateStatus'])->name('barber.reservations.update-status');
});

// Rutas públicas de reserva (AJAX / Inertia - En web.php para que Inertia las encuentre)
Route::get('/barberia/{slug}', [PublicBookingController::class, 'show'])
    ->name('public.booking.show');

Route::post('/barberia/availability/check', [PublicBookingController::class, 'availability'])
    ->name('public.booking.availability')
    ->withoutMiddleware(['csrf']); // Quitamos CSRF para estas rutas públicas

Route::post('/barberia/reservations', [PublicBookingController::class, 'store'])
    ->name('public.booking.store')
    ->withoutMiddleware(['csrf']);

Route::post('/barberia/reservations/{reservation}/receipt', [PublicBookingController::class, 'uploadReceipt'])
    ->name('public.booking.receipt')
    ->withoutMiddleware(['csrf']);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
