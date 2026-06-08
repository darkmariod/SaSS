<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\BarberController;

Route::get('/', function () {
    // Authenticated → dashboard; otherwise → Linktree de la barbería demo
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return redirect()->route('public.shop.show', ['slug' => 'barberia-demo']);
});

Route::get('/health', HealthController::class);

// Barber routes (solo para barberos)
Route::middleware(['auth', 'role:barber'])->group(function () {
    Route::get('/barbero/mis-citas', [BarberController::class, 'myAppointments'])->name('barber.appointments');
    Route::patch('/barbero/reservations/{reservation}/status', [BarberController::class, 'updateStatus'])->name('barber.reservations.update-status');
    Route::post('/barbero/reservations/{reservation}/payment/confirm', [BarberController::class, 'confirmPayment'])->name('barber.reservations.confirm-payment');
    Route::post('/barbero/reservations/{reservation}/payment/reject', [BarberController::class, 'rejectPayment'])->name('barber.reservations.reject-payment');
});

// Rutas públicas de reserva (AJAX / Inertia - En web.php para que Inertia las encuentre)
Route::get('/barberia/{slug}', [PublicBookingController::class, 'shop'])
    ->name('public.shop.show');

Route::get('/barberia/{slug}/reservar', [PublicBookingController::class, 'show'])
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

// API para el calendario interno de Filament
Route::get('/api/calendar/events', [App\Http\Controllers\CalendarController::class, 'events'])
    ->middleware('auth');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Selección de plan post-registro
Route::middleware(['auth'])->group(function () {
    Route::get('/register/plan', [PlanController::class, 'select'])->name('register.plan');
    Route::post('/register/plan', [PlanController::class, 'store'])->name('register.plan.store');
});

require __DIR__.'/auth.php';
