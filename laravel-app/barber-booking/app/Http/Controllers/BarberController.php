<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BarberController extends Controller
{
    public function myAppointments(Request $request): Response
    {
        $user = $request->user();

        // Solo ve sus citas donde él es el consultor (barbero)
        $reservations = Reservation::with(['barberShop', 'service', 'addonService'])
            ->where('consultant_id', $user->id)
            ->orderBy('reservation_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();

        return Inertia::render('Barber/MyAppointments', [
            'reservations' => $reservations,
        ]);
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        // Verificar que la reserva pertenezca a este barbero
        if ($reservation->consultant_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validate([
            'reservation_status' => ['required', 'in:confirmada,cancelada'],
        ]);

        $reservation->update($validated);

        return back()->with('success', 'Estado actualizado correctamente.');
    }
}
