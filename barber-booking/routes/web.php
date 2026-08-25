<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PublicBookingController;
use App\Http\Controllers\BarberController;

// La raíz es la página de venta del producto: quien llega escribiendo sólo la
// dirección es un dueño de barbería evaluando el sistema, no el cliente de una
// barbería concreta. Los clientes entran por /barberia/{slug}, que es lo que
// lleva el código QR.
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }

    $demo = \App\Models\BarberShop::where('is_active', true)->orderBy('id')->first();

    return Inertia::render('Landing', [
        'planes' => \App\Models\Plan::where('is_active', true)
            ->orderBy('monthly_price')
            ->get(['id', 'name', 'max_barbers', 'setup_price', 'monthly_price']),
        'demoUrl' => $demo ? route('public.shop.show', ['slug' => $demo->slug]) : null,
        'whatsapp' => config('billing.whatsapp'),
    ]);
})->name('landing');

// Enlace estable a una barbería viva, para mostrarla a un comprador sin tener
// que recordar el slug del momento.
Route::get('/demo', function () {
    $demo = \App\Models\BarberShop::where('is_active', true)->orderBy('id')->first();

    abort_if(! $demo, 404, 'Todavía no hay ninguna barbería publicada.');

    return redirect()->route('public.shop.show', ['slug' => $demo->slug]);
})->name('demo');

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

// Public booking is unauthenticated + CSRF-exempt, so rate limiting is the
// only thing standing between the shop's agenda and spam/flooding.
Route::post('/barberia/availability/check', [PublicBookingController::class, 'availability'])
    ->name('public.booking.availability')
    ->middleware('throttle:30,1')
    ->withoutMiddleware(['csrf']);

Route::post('/barberia/reservations', [PublicBookingController::class, 'store'])
    ->name('public.booking.store')
    ->middleware('throttle:10,1')
    ->withoutMiddleware(['csrf']);

Route::post('/barberia/reservations/{reservation}/receipt', [PublicBookingController::class, 'uploadReceipt'])
    ->name('public.booking.receipt')
    ->middleware('throttle:10,1')
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
