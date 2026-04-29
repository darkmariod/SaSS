<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;

class ClientController extends Controller
{
    /**
     * @OA\Get(
     *     path="/my-reservations",
     *     summary="Listar reservas del cliente autenticado",
     *     tags={"Cliente"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Reservas obtenidas correctamente"
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
            $clientRoleId = config('roles.client', 3);

            if ($user->rol_id != $clientRoleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado. Solo clientes pueden acceder.'
                ], 403);
            }

            $query = \App\Models\Reservation::with([
                    'professional:id,nombres,apellidos,email,teléfono'
                ])
                ->where('user_id', $user->id);

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
     *     path="/my-payments",
     *     summary="Listar historial de pagos del cliente autenticado",
     *     tags={"Cliente"},
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
     *         description="Pagos obtenidos correctamente"
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
    public function myPayments(Request $request)
    {
        try {

            $user = $request->user();
            $clientRoleId = config('roles.client', 3);

            if ($user->rol_id != $clientRoleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado. Solo clientes pueden acceder.'
                ], 403);
            }

            $payments = \App\Models\ReservationDetail::with([
                    'reservation.professional:id,nombres,apellidos,email,teléfono'
                ])
                ->whereHas('reservation', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                    ->where('payment_status', 'pagado');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'message' => 'Pagos obtenidos correctamente',
                'data' => $payments
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pagos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/my-profile",
     *     summary="Obtener perfil del usuario autenticado",
     *     tags={"Cliente"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Perfil obtenido correctamente"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function myProfile(Request $request)
    {
        try {

            $user = $request->user();

            return response()->json([
                'success' => true,
                'message' => 'Perfil obtenido correctamente',
                'data' => [
                    'id' => $user->id,
                    'nombres' => $user->nombres,
                    'apellidos' => $user->apellidos,
                    'email' => $user->email,
                    'telefono' => $user->teléfono,
                    'foto' => $user->foto,
                    'rol_id' => $user->rol_id,
                    'created_at' => $user->created_at
                ]
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/my-profile",
     *     summary="Actualizar perfil del usuario autenticado",
     *     tags={"Cliente"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombres","apellidos","telefono"},
     *             @OA\Property(property="nombres", type="string", example="Juan"),
     *             @OA\Property(property="apellidos", type="string", example="Perez"),
     *             @OA\Property(property="telefono", type="string", example="987654321"),
     *             @OA\Property(property="foto", type="string", example="avatar.jpg")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Perfil actualizado correctamente"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function updateProfile(Request $request)
    {
        try {

            $user = $request->user();

            $validated = $request->validate([
                'nombres' => 'required|string|max:255',
                'apellidos' => 'required|string|max:255',
                'telefono' => 'required|string|max:20',
                'foto' => 'nullable|string|max:255'
            ]);

            $user->nombres = $validated['nombres'];
            $user->apellidos = $validated['apellidos'];
            $user->teléfono = $validated['telefono'];

            if (isset($validated['foto'])) {
                $user->foto = $validated['foto'];
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'data' => [
                    'id' => $user->id,
                    'nombres' => $user->nombres,
                    'apellidos' => $user->apellidos,
                    'email' => $user->email,
                    'telefono' => $user->teléfono,
                    'foto' => $user->foto
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/change-password",
     *     summary="Cambiar contraseña del usuario autenticado",
     *     tags={"Cliente"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="12345678"),
     *             @OA\Property(property="new_password", type="string", example="newPassword123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newPassword123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña actualizada correctamente"
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function changePassword(Request $request)
    {
        try {

            $user = $request->user();

            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed'
            ]);

            // Verificar contraseña actual
            if (!\Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ], 400);
            }

            // Verificar que no sea la misma contraseña
            if (\Hash::check($validated['new_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La nueva contraseña no puede ser igual a la actual'
                ], 400);
            }

            $user->password = \Hash::make($validated['new_password']);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/reservations/{id}",
     *     summary="Cancelar una reserva del cliente autenticado",
     *     description="Permite al cliente cancelar una reserva confirmada con mínimo 24 horas de anticipación. No hay devolución de pago. El motivo es obligatorio.",
     *     tags={"Cliente"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la reserva a cancelar",
     *         required=true,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cancellation_reason"},
     *             @OA\Property(
     *                 property="cancellation_reason",
     *                 type="string",
     *                 minLength=10,
     *                 maxLength=500,
     *                 example="Tuve un imprevisto personal y no podré asistir."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reserva cancelada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reserva cancelada correctamente")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Error de reglas de negocio",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Debes cancelar con mínimo 24 horas de anticipación")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No puedes cancelar esta reserva")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Reserva no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Reserva no encontrada")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error de validación"),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 example={"cancellation_reason": {"El motivo es obligatorio"}}
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
    public function cancelReservation(Request $request, $id)
    {
        try {

            $user = $request->user();
            $clientRoleId = config('roles.client', 3);

            if ($user->rol_id != $clientRoleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado. Solo clientes pueden cancelar reservas.'
                ], 403);
            }

            $validated = $request->validate([
                'cancellation_reason' => 'required|string|min:10|max:500'
            ]);

            $reservation = \App\Models\Reservation::find($id);

            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reserva no encontrada'
                ], 404);
            }

            if ($reservation->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes cancelar esta reserva'
                ], 403);
            }

            if ($reservation->reservation_status !== 'confirmada') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar reservas confirmadas'
                ], 400);
            }

            // Combinar fecha y hora
            $reservationDateTime = \Carbon\Carbon::parse(
                $reservation->reservation_date->format('Y-m-d') . ' ' . $reservation->start_time->format('H:i')
            );

            if ($reservationDateTime->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes cancelar una reserva pasada'
                ], 400);
            }

            if (now()->diffInHours($reservationDateTime, false) < 24) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes cancelar con mínimo 24 horas de anticipación'
                ], 400);
            }

            $reservation->reservation_status = 'cancelada';
            $reservation->cancellation_reason = $validated['cancellation_reason'];
            $reservation->save();

            \Log::info('Reserva cancelada correctamente', ['reservation_id' => $reservation->id]);

            // 🔥 Notificaciones
            $this->sendCancellationEmail($reservation);
            $this->sendCancellationWhatsApp($reservation);

            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada correctamente'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function sendCancellationEmail($reservation)
    {
        try {

            $user = $reservation->client;

            if (!$user || !$user->email) {
                \Log::warning('Usuario sin email válido para cancelación');
                return;
            }

            $message = "Hola {$user->nombres},\n\n"
                . "Tu reserva ha sido cancelada correctamente.\n\n"
                . "Fecha: {$reservation->reservation_date}\n"
                . "Hora: {$reservation->start_time->format('H:i')}\n"
                . "Motivo: {$reservation->cancellation_reason}\n\n"
                . "Gracias por informarnos.";

            Mail::raw($message, function ($mail) use ($user) {
                $mail->to($user->email)
                    ->subject('Reserva cancelada correctamente');
            });

            \Log::info('Correo de cancelación enviado correctamente');

        } catch (\Exception $e) {
            \Log::error('Error enviando correo de cancelación', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendCancellationWhatsApp($reservation)
    {
        try {

            $user = $reservation->client;

            if (!$user || !$user->teléfono) {
                \Log::warning('Usuario sin teléfono válido para cancelación');
                return;
            }

            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.whatsapp_from');

            $twilio = new Client($sid, $token);

            $phone = trim($user->teléfono);

            if (!str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }

            $body = "❌ *Reserva Cancelada*\n\n"
                . "📅 Fecha: {$reservation->reservation_date}\n"
                . "🕒 Hora: {$reservation->start_time->format('H:i')}\n"
                . "📝 Motivo: {$reservation->cancellation_reason}\n\n"
                . "Tu cancelación fue procesada correctamente.";

            $twilio->messages->create(
                "whatsapp:{$phone}",
                [
                    "from" => $from,
                    "body" => $body
                ]
            );

            \Log::info('WhatsApp de cancelación enviado correctamente');

        } catch (\Exception $e) {

            \Log::error('Error enviando WhatsApp de cancelación', [
                'error' => $e->getMessage()
            ]);
        }
    }


}
