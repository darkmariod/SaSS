<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Service;
use App\Models\ConsultantAvailability;
use Carbon\Carbon;

class ConsultantController extends Controller
{
    public function index(Request $request)
    {
        try {
            $consultantRoleId = 2;

            $consultants = User::where('rol_id', $consultantRoleId)
                ->select('id', 'nombres', 'apellidos', 'email', 'telefono', 'foto')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Listado obtenido correctamente',
                'data' => $consultants
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener consultores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function calendar(Request $request, $id)
    {
        try {
            $consultantRoleId = 2;

            if (!$request->has('date')) {
                return response()->json([
                    'success' => false,
                    'message' => 'El parámetro date es obligatorio'
                ], 400);
            }

            $date = $request->query('date');

            if (!\DateTime::createFromFormat('Y-m-d', $date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato de fecha inválido (YYYY-MM-DD)'
                ], 400);
            }

            $consultant = User::where('id', $id)
                ->where('rol_id', $consultantRoleId)
                ->first();

            if (!$consultant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Consultor no encontrado'
                ], 404);
            }

            $services = Service::where('consultant_id', $id)
                ->where('is_active', true)
                ->get();

            $dayOfWeek = Carbon::parse($date)->dayOfWeek;
            $availability = ConsultantAvailability::where('consultant_id', $id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_available', true)
                ->first();

            $existingReservations = \App\Models\Reservation::where('consultant_id', $id)
                ->whereDate('reservation_date', $date)
                ->where('reservation_status', 'confirmada')
                ->where('payment_status', 'pagado')
                ->get()
                ->map(function ($reservation) {
                    return Carbon::parse($reservation->start_time)->format('H:i');
                })
                ->toArray();

            if (!$availability) {
                return response()->json([
                    'success' => true,
                    'message' => 'Consultor no disponible este día',
                    'data' => [
                        'consultant_id' => $consultant->id,
                        'consultant_name' => $consultant->nombres . ' ' . $consultant->apellidos,
                        'date' => $date,
                        'services' => $services,
                        'slots' => [],
                        'available' => false
                    ]
                ], 200);
            }

            $slots = [];
            $startHour = Carbon::parse($availability->start_time)->hour;
            $endHour = Carbon::parse($availability->end_time)->hour;

            for ($hour = $startHour; $hour < $endHour; $hour++) {
                $start = sprintf('%02d:00', $hour);
                $end = sprintf('%02d:00', $hour + 1);
                $isAvailable = !in_array($start, $existingReservations);

                $slots[] = [
                    'start_time' => $start,
                    'end_time' => $end,
                    'available' => $isAvailable
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Calendario generado correctamente',
                'data' => [
                    'consultant_id' => $consultant->id,
                    'consultant_name' => $consultant->nombres . ' ' . $consultant->apellidos,
                    'date' => $date,
                    'services' => $services,
                    'slots' => $slots
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar calendario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function myReservations(Request $request)
    {
        try {
            $user = $request->user();
            $consultantRoleId = 2;

            if ($user->rol_id != $consultantRoleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado. Solo consultores pueden acceder.'
                ], 403);
            }

            $query = \App\Models\Reservation::with([
                    'client:id,nombres,apellidos,email,telefono'
                ])
                ->where('consultant_id', $user->id)
                ->where('reservation_status', 'confirmada')
                ->where('payment_status', 'pagado');

            if ($request->has('date')) {
                if (!\DateTime::createFromFormat('Y-m-d', $request->date)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de fecha inválido (YYYY-MM-DD)'
                    ], 400);
                }
                $query->whereDate('reservation_date', $request->date);
            }

            $reservations = $query
                ->orderBy('reservation_date', 'desc')
                ->orderBy('start_time', 'asc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Reservas obtenidas correctamente',
                'data' => $reservations
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reservas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $consultantRoleId = 2;

            $consultant = \App\Models\User::where('id', $id)
                ->where('rol_id', $consultantRoleId)
                ->first();

            if (!$consultant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Consultor no encontrado'
                ], 404);
            }

            $confirmedReservations = \App\Models\Reservation::where('consultant_id', $id)
                ->where('reservation_status', 'confirmada')
                ->where('payment_status', 'pagado');

            $totalReservations = $confirmedReservations->count();
            $totalRevenue = $confirmedReservations->sum('total_amount');

            return response()->json([
                'success' => true,
                'message' => 'Detalle obtenido correctamente',
                'data' => [
                    'id' => $consultant->id,
                    'nombres' => $consultant->nombres,
                    'apellidos' => $consultant->apellidos,
                    'email' => $consultant->email,
                    'telefono' => $consultant->telefono,
                    'foto' => $consultant->foto,
                    'total_reservations_confirmed' => $totalReservations,
                    'total_revenue' => number_format($totalRevenue, 2)
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function myCalendar(Request $request)
    {
        try {
            $user = $request->user();
            $consultantRoleId = 2;

            if ($user->rol_id != $consultantRoleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado. Solo consultores pueden acceder.'
                ], 403);
            }

            if (!$request->has('date')) {
                return response()->json([
                    'success' => false,
                    'message' => 'El parámetro date es obligatorio'
                ], 400);
            }

            $date = $request->query('date');

            if (!\DateTime::createFromFormat('Y-m-d', $date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato de fecha inválido (YYYY-MM-DD)'
                ], 400);
            }

            $services = Service::where('consultant_id', $user->id)
                ->where('is_active', true)
                ->get();

            $dayOfWeek = Carbon::parse($date)->dayOfWeek;
            $availability = ConsultantAvailability::where('consultant_id', $user->id)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_available', true)
                ->first();

            $reservations = \App\Models\Reservation::with([
                    'client:id,nombres,apellidos,email,telefono',
                    'service:id,name,price_per_hour'
                ])
                ->where('consultant_id', $user->id)
                ->whereDate('reservation_date', $date)
                ->where('reservation_status', 'confirmada')
                ->where('payment_status', 'pagado')
                ->get();

            $reservedTimes = $reservations->map(function ($r) {
                return Carbon::parse($r->start_time)->format('H:i');
            })->toArray();

            if (!$availability) {
                return response()->json([
                    'success' => true,
                    'message' => 'No disponible este día',
                    'data' => [
                        'consultant_id' => $user->id,
                        'consultant_name' => $user->nombres . ' ' . $user->apellidos,
                        'date' => $date,
                        'services' => $services,
                        'slots' => [],
                        'available' => false
                    ]
                ], 200);
            }

            $slots = [];
            $startHour = Carbon::parse($availability->start_time)->hour;
            $endHour = Carbon::parse($availability->end_time)->hour;

            for ($hour = $startHour; $hour < $endHour; $hour++) {
                $start = sprintf('%02d:00', $hour);
                $end = sprintf('%02d:00', $hour + 1);

                $reservationData = $reservations->first(function ($r) use ($start) {
                    return Carbon::parse($r->start_time)->format('H:i') === $start;
                });

                $slots[] = [
                    'start_time' => $start,
                    'end_time' => $end,
                    'available' => !in_array($start, $reservedTimes),
                    'reservation' => $reservationData ? [
                        'id' => $reservationData->id,
                        'client' => $reservationData->client,
                        'total_amount' => $reservationData->total_amount
                    ] : null
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Calendario obtenido correctamente',
                'data' => [
                    'consultant_id' => $user->id,
                    'consultant_name' => $user->nombres . ' ' . $user->apellidos,
                    'date' => $date,
                    'services' => $services,
                    'slots' => $slots
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar calendario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAvailability(Request $request, $id)
    {
        try {
            $consultantRoleId = 2;

            $consultant = User::where('id', $id)
                ->where('rol_id', $consultantRoleId)
                ->first();

            if (!$consultant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Consultor no encontrado'
                ], 404);
            }

            $availability = ConsultantAvailability::where('consultant_id', $id)
                ->orderBy('day_of_week')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $availability
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener disponibilidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storeAvailability(Request $request)
    {
        try {
            $user = $request->user();
            $consultantRoleId = 2;

            if ($user->rol_id != $consultantRoleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            $validated = $request->validate([
                'day_of_week' => 'required|integer|min:0|max:6',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'is_available' => 'sometimes|boolean'
            ]);

            $exists = ConsultantAvailability::where('consultant_id', $user->id)
                ->where('day_of_week', $validated['day_of_week'])
                ->first();

            if ($exists) {
                $exists->update([
                    'start_time' => $validated['start_time'],
                    'end_time' => $validated['end_time'],
                    'is_available' => $validated['is_available'] ?? true
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Disponibilidad actualizada',
                    'data' => $exists
                ], 200);
            }

            $availability = ConsultantAvailability::create([
                'consultant_id' => $user->id,
                'day_of_week' => $validated['day_of_week'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'is_available' => $validated['is_available'] ?? true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Disponibilidad configurada',
                'data' => $availability
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al configurar disponibilidad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateAvailability(Request $request, $id)
    {
        try {
            $user = $request->user();

            $availability = ConsultantAvailability::find($id);

            if (!$availability) {
                return response()->json([
                    'success' => false,
                    'message' => 'Disponibilidad no encontrada'
                ], 404);
            }

            if ($availability->consultant_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            $validated = $request->validate([
                'day_of_week' => 'sometimes|integer|min:0|max:6',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i|after:start_time',
                'is_available' => 'sometimes|boolean'
            ]);

            $availability->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Disponibilidad actualizada',
                'data' => $availability
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyAvailability(Request $request, $id)
    {
        try {
            $user = $request->user();

            $availability = ConsultantAvailability::find($id);

            if (!$availability) {
                return response()->json([
                    'success' => false,
                    'message' => 'Disponibilidad no encontrada'
                ], 404);
            }

            if ($availability->consultant_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado'
                ], 403);
            }

            $availability->delete();

            return response()->json([
                'success' => true,
                'message' => 'Disponibilidad eliminada'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
