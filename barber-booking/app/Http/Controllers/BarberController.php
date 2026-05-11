<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BarberController extends Controller
{
    public function myAppointments(Request $request): Response
    {
        $user = $request->user();

        $reservations = Reservation::with(['barberShop', 'service', 'addonService', 'detail', 'transfer'])
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
        if ($reservation->consultant_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validate([
            'reservation_status' => ['required', 'in:confirmada,cancelada'],
        ]);

        $reservation->update($validated);

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    public function confirmPayment(Request $request, Reservation $reservation)
    {
        if ($reservation->consultant_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        DB::transaction(function () use ($reservation, $request) {
            $transfer = $reservation->transfer;

            if ($transfer) {
                $transfer->confirm($request->user()->id);
            } else {
                $reservation->update([
                    'payment_status' => 'pagado',
                    'reservation_status' => 'confirmada',
                ]);

                $reservation->detail?->update([
                    'payment_status' => 'pagado',
                    'paid_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Pago confirmado correctamente.');
    }

    public function rejectPayment(Request $request, Reservation $reservation)
    {
        if ($reservation->consultant_id !== $request->user()->id) {
            abort(403, 'No autorizado');
        }

        DB::transaction(function () use ($reservation, $request) {
            $transfer = $reservation->transfer;

            if ($transfer) {
                $transfer->reject($request->user()->id);
            } else {
                $reservation->update([
                    'payment_status' => 'rechazado',
                    'reservation_status' => 'cancelada',
                ]);

                $reservation->detail?->update([
                    'payment_status' => 'rechazado',
                ]);
            }
        });

        return back()->with('success', 'Pago rechazado.');
    }
}
