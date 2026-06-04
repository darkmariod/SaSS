<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function events(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([], 401);
        }

        $start = $request->input('start'); // ISO string from FullCalendar
        $end = $request->input('end');

        $query = Reservation::query()
            ->with(['service', 'addonService', 'consultant', 'barberShop'])
            ->whereIn('reservation_status', ['pendiente', 'confirmada', 'completada']);

        // Owner: solo sus barberías
        if ($user->hasRole('owner')) {
            $query->whereHas('barberShop', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            });
        }

        // Barber: solo sus reservas
        if ($user->hasRole('barber')) {
            $query->where('consultant_id', $user->id);
        }

        // Filter by date range
        if ($start) {
            $query->whereDate('reservation_date', '>=', Carbon::parse($start)->toDateString());
        }
        if ($end) {
            $query->whereDate('reservation_date', '<=', Carbon::parse($end)->toDateString());
        }

        $reservations = $query->orderBy('reservation_date')->orderBy('start_time')->get();

        $events = $reservations->map(function (Reservation $r) {
            $color = match ($r->reservation_status) {
                'confirmada' => '#10b981',   // green
                'pendiente' => '#f59e0b',     // amber
                'completada' => '#6366f1',    // indigo
                'cancelada' => '#ef4444',     // red
                default => '#6b7280',         // gray
            };

            $titleParts = [$r->service?->name ?? 'Servicio'];

            if ($r->consultant) {
                $titleParts[] = '— ' . $r->consultant->name;
            }

            return [
                'id' => (string) $r->id,
                'title' => implode(' ', $titleParts),
                'start' => $r->reservation_date . 'T' . $r->start_time,
                'end' => $r->reservation_date . 'T' . $r->end_time,
                'color' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'customer_name' => $r->customer_name,
                    'customer_phone' => $r->customer_phone,
                    'customer_email' => $r->customer_email,
                    'service' => $r->service?->name,
                    'addon' => $r->addonService?->name,
                    'barber' => $r->consultant?->name,
                    'status' => $r->reservation_status,
                    'payment_status' => $r->payment_status,
                    'total' => (float) $r->total_amount,
                    'barber_shop' => $r->barberShop?->name,
                    'id' => $r->id,
                ],
            ];
        });

        return response()->json($events);
    }
}
