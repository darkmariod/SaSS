<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

/**
 * @OA\Tag(
 *     name="Consultores",
 *     description="Endpoints relacionados con los asesores"
 * )
 */
class ConsultantController extends Controller
{
    /**
     * @OA\Get(
     *     path="/consultants",
     *     summary="Listar asesores paginados",
     *     tags={"Consultores"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Listado de consultores paginado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Listado obtenido correctamente"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=25),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="nombres", type="string", example="Carlos"),
     *                         @OA\Property(property="apellidos", type="string", example="Ramirez"),
     *                         @OA\Property(property="email", type="string", example="consultor@test.com"),
     *                         @OA\Property(property="telefono", type="string", example="987654321"),
     *                         @OA\Property(property="foto", type="string", example="foto.jpg")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {

            // ⚠️ Ajusta el ID del rol consultor si es diferente
            $consultantRoleId = 2;

            $consultants = User::where('rol_id', $consultantRoleId)
                ->select('id', 'nombres', 'apellidos', 'email', 'teléfono', 'foto')
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

    /**
     * @OA\Get(
     *     path="/consultants/{id}/calendar",
     *     summary="Obtener calendario dinámico de un consultor por fecha",
     *     tags={"Consultores"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del consultor",
     *         @OA\Schema(type="integer", example=2)
     *     ),
     *
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=true,
     *         description="Fecha en formato YYYY-MM-DD",
     *         @OA\Schema(type="string", example="2026-03-20")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Calendario generado correctamente"
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Fecha inválida"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Consultor no encontrado"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function calendar(Request $request, $id)
    {
        try {

            $consultantRoleId = 2; // Ajustar si es diferente
            $pricePerHour = 50;

            // Validar fecha
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

            // Verificar consultor
            $consultant = User::where('id', $id)
                ->where('rol_id', $consultantRoleId)
                ->first();

            if (!$consultant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Consultor no encontrado'
                ], 404);
            }

            // Obtener reservas existentes del día
            $existingReservations = \App\Models\Reservation::where('consultant_id', $id)
                ->whereDate('reservation_date', $date)
                ->where('reservation_status', 'confirmada')
                ->where('payment_status', 'pagado')
                ->get()
                ->map(function ($reservation) {
                    return \Carbon\Carbon::parse($reservation->start_time)->format('H:i');
                })
                ->toArray();

            // Generar slots 09:00–18:00
            $slots = [];
            $startHour = 9;
            $endHour = 18;

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
                    'price_per_hour' => $pricePerHour,
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

    /**
     * @OA\Get(
     *     path="/consultant/reservations",
     *     summary="Listar reservas del consultor autenticado",
     *     tags={"Consultores"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Filtrar por fecha YYYY-MM-DD",
     *         required=false,
     *         @OA\Schema(type="string", example="2024-10-23")
     *     ),
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Listado de reservas obtenido correctamente"
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function myReservations(Request $request)
    {
        try {

            $user = $request->user();

            // ⚠️ Ajustar ID del rol consultor si es diferente
            $consultantRoleId = 2;

            if ($user->rol_id != $consultantRoleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado. Solo consultores pueden acceder.'
                ], 403);
            }

            $query = \App\Models\Reservation::with([
                    'client:id,nombres,apellidos,email,teléfono'
                ])
                ->where('consultant_id', $user->id)
                ->where('reservation_status', 'confirmada')
                ->where('payment_status', 'pagado');

            // Filtro opcional por fecha
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

    /**
     * @OA\Get(
     *     path="/consultants/{id}",
     *     summary="Obtener detalle de un consultor",
     *     tags={"Consultores"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del consultor",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Detalle del consultor obtenido correctamente"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Consultor no encontrado"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function show($id)
    {
        try {

            $consultantRoleId = 2; // Ajustar si es diferente

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
                    'telefono' => $consultant->teléfono,
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

    /**
     * @OA\Get(
     *     path="/consultant/calendar",
     *     summary="Obtener calendario del consultor autenticado",
     *     tags={"Consultores"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         description="Fecha en formato YYYY-MM-DD",
     *         required=true,
     *         @OA\Schema(type="string", example="2024-10-23")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Calendario obtenido correctamente"
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado"
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Fecha inválida"
     *     )
     * )
     */
    public function myCalendar(Request $request)
    {
        try {

            $user = $request->user();
            $consultantRoleId = 2; // Ajustar si es diferente
            $pricePerHour = 50;

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

            $reservations = \App\Models\Reservation::with([
                    'client:id,nombres,apellidos,email,teléfono'
                ])
                ->where('consultant_id', $user->id)
                ->whereDate('reservation_date', $date)
                ->where('reservation_status', 'confirmada')
                ->where('payment_status', 'pagado')
                ->get();

            $reservedTimes = $reservations->map(function ($r) {
                return \Carbon\Carbon::parse($r->start_time)->format('H:i');
            })->toArray();

            $slots = [];
            $startHour = 9;
            $endHour = 18;

            for ($hour = $startHour; $hour < $endHour; $hour++) {

                $start = sprintf('%02d:00', $hour);
                $end = sprintf('%02d:00', $hour + 1);

                $reservationData = $reservations->first(function ($r) use ($start) {
                    return \Carbon\Carbon::parse($r->start_time)->format('H:i') === $start;
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
                    'price_per_hour' => $pricePerHour,
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

}
