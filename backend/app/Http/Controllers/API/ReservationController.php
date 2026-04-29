<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Services\ReservationService;
use App\Models\Reservation;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * @OA\Post(
     *     path="/reservations",
     *     summary="Crear una nueva reserva",
     *     tags={"Reservas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"branch_id","professional_id","service_id","reservation_date","start_time"},
     *         @OA\Property(property="branch_id", type="integer", example=1),
     *         @OA\Property(property="professional_id", type="integer", example=2),
     *         @OA\Property(property="service_id", type="integer", example=1),
     *         @OA\Property(property="reservation_date", type="string", format="date", example="2026-05-01"),
     *         @OA\Property(property="start_time", type="string", example="10:00")
     *     )),
     *     @OA\Response(response=201, description="Reserva creada")
     * )
     */
    public function store(StoreReservationRequest $request)
    {
        try {
            $reservation = $this->reservationService->createReservation(
                $request->user()->id,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Reserva creada correctamente',
                'data' => new ReservationResource($reservation)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/my-reservations",
     *     summary="Mis reservas (cliente)",
     *     tags={"Reservas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Lista de reservas")
     * )
     */
    public function myReservations(Request $request)
    {
        $reservations = Reservation::with(['branch', 'professional', 'items.service'])
            ->where('user_id', $request->user()->id)
            ->orderBy('reservation_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => ReservationResource::collection($reservations)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/reservations/{id}/cancel",
     *     summary="Cancelar reserva",
     *     tags={"Reservas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"cancellation_reason"},
     *         @OA\Property(property="cancellation_reason", type="string", minLength=10)
     *     )),
     *     @OA\Response(response=200, description="Reserva cancelada")
     * )
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:500'
        ]);

        $reservation = Reservation::where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($reservation->reservation_status !== 'pending' && $reservation->reservation_status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede cancelar esta reserva'
            ], 400);
        }

        $reservation->update([
            'reservation_status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reserva cancelada correctamente'
        ]);
    }
}
