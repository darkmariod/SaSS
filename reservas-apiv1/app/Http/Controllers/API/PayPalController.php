<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use App\Models\PayPalOrder;
use App\Models\Reservation;
use Carbon\Carbon;
use Srmklive\PayPal\Services\PayPal;

use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationConfirmedMail;
use App\Models\User;

use Twilio\Rest\Client;

class PayPalController extends Controller
{
    /**
     * @OA\Post(
     *     path="/paypal/create-order",
     *     operationId="createPayPalOrder",
     *     summary="Crear orden de pago en PayPal",
     *     tags={"Pagos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"consultant_id","date","hours"},
     *             @OA\Property(property="consultant_id", type="integer", example=5),
     *             @OA\Property(property="date", type="string", example="2024-10-23"),
     *             @OA\Property(
     *                 property="hours",
     *                 type="array",
     *                 @OA\Items(type="string", example="10:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Orden creada correctamente")
     * )
     */
    public function createOrder(Request $request)
    {
        try {

            $user = $request->user();
            $clientRoleId = 3;
            $pricePerHour = 50;

            if ($user->rol_id != $clientRoleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo clientes pueden realizar pagos'
                ], 403);
            }

            $validated = $request->validate([
                'consultant_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'hours' => 'required|array|min:1'
            ]);

            $date = $validated['date'];
            $consultantId = $validated['consultant_id'];
            $hours = collect($validated['hours'])->sort()->values();

            // Fecha no puede ser pasada
            if ($date < now()->format('Y-m-d')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes reservar fechas pasadas'
                ], 400);
            }

            // Validar consecutividad
            for ($i = 0; $i < count($hours) - 1; $i++) {

                $current = \Carbon\Carbon::createFromFormat('H:i', $hours[$i]);
                $next = \Carbon\Carbon::createFromFormat('H:i', $hours[$i + 1]);

                if ($current->copy()->addHour()->format('H:i') !== $next->format('H:i')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Las horas deben ser consecutivas'
                    ], 400);
                }
            }

            // Validar disponibilidad
            $existingReservations = \App\Models\Reservation::where('consultant_id', $consultantId)
                ->whereDate('reservation_date', $date)
                ->whereIn('start_time', $hours)
                ->where('reservation_status', 'confirmada')
                ->where('payment_status', 'pagado')
                ->exists();

            if ($existingReservations) {
                return response()->json([
                    'success' => false,
                    'message' => 'Una o más horas ya están reservadas'
                ], 400);
            }

            $totalAmount = count($hours) * $pricePerHour;

            // 🔥 Generar referencia UUID
            $reference = \Illuminate\Support\Str::uuid()->toString();

            // 🔥 Guardar metadata en tabla paypal_orders
            $paypalOrder = \App\Models\PayPalOrder::create([
                'reference' => $reference,
                'user_id' => $user->id,
                'consultant_id' => $consultantId,
                'reservation_date' => $date,
                'hours' => $hours,
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]);

            // 🔥 Crear orden PayPal
            $provider = new \Srmklive\PayPal\Services\PayPal();
            $provider->setApiCredentials(config('paypal'));
            $paypalToken = $provider->getAccessToken();
            $provider->setAccessToken($paypalToken);

            $order = $provider->createOrder([
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "reference_id" => $reference,
                        "custom_id" => $reference,
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => $totalAmount
                        ]
                    ]
                ],
                "application_context" => [
                    "return_url" => url('/paypal/success'),
                    "cancel_url" => url('/paypal/cancel')
                ]
            ]);

            // 🔥 Guardar order_id real de PayPal
            $paypalOrder->update([
                'paypal_order_id' => $order['id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Orden creada correctamente',
                'data' => [
                    'reference' => $reference,
                    'order_id' => $order['id'],
                    'approval_url' => collect($order['links'])
                        ->firstWhere('rel', 'approve')['href'] ?? null,
                    'total_amount' => $totalAmount
                ]
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al crear orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/paypal/webhook",
     *     summary="Webhook PayPal para confirmar pago",
     *     description="Endpoint llamado por PayPal cuando el pago se completa",
     *     tags={"Pagos"},
     *     @OA\Response(
     *         response=200,
     *         description="Pago confirmado y reservas creadas correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pago confirmado y reservas creadas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación o pago no completado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pago no completado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error en webhook"),
     *             @OA\Property(property="error", type="string", example="Mensaje de excepción")
     *         )
     *     )
     * )
     */
    public function webhook(Request $request)
    {
        try {

            \Log::info('================ PAYPAL WEBHOOK START =================');

            $payload = $request->all();
            \Log::info('Payload:', $payload);

            $eventType = $payload['event_type'] ?? null;
            \Log::info('Event Type:', ['event_type' => $eventType]);

            /*
            ====================================================
            🔹 1️⃣ SI ES APPROVED → CAPTURAR AUTOMÁTICAMENTE
            ====================================================
            */
            if ($eventType === 'CHECKOUT.ORDER.APPROVED') {

                \Log::info('Evento APPROVED detectado. Iniciando captura automática...');

                $resource = $payload['resource'] ?? [];
                $orderId = $resource['id'] ?? null;

                if (!$orderId) {
                    \Log::error('No se encontró order_id para capturar');
                    return response()->json(['message' => 'Order ID no encontrado'], 400);
                }

                $provider = new PayPal();
                $provider->setApiCredentials(config('paypal'));
                $accessToken = $provider->getAccessToken();
                $provider->setAccessToken($accessToken);

                $captureResponse = $provider->capturePaymentOrder($orderId);

                \Log::info('Respuesta de captura automática:', $captureResponse);

                return response()->json([
                    'message' => 'Orden capturada automáticamente'
                ], 200);
            }

            /*
            ====================================================
            🔹 2️⃣ SI ES CAPTURE COMPLETED → CREAR RESERVAS
            ====================================================
            */
            if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {

                \Log::info('Evento CAPTURE COMPLETED detectado');

                $resource = $payload['resource'] ?? [];

                $reference = $resource['custom_id'] ?? null;
                $paypalOrderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;
                $status = $resource['status'] ?? null;
                $transactionId = $resource['id'] ?? null;

                \Log::info('Datos extraídos:', [
                    'reference' => $reference,
                    'paypal_order_id' => $paypalOrderId,
                    'status' => $status,
                    'transaction_id' => $transactionId
                ]);

                if (!$reference || $status !== 'COMPLETED') {
                    \Log::error('Datos inválidos en CAPTURE COMPLETED');
                    return response()->json(['message' => 'Datos inválidos'], 400);
                }

                $paypalOrder = PayPalOrder::where('reference', $reference)
                    ->where('status', 'pending')
                    ->first();

                if (!$paypalOrder) {
                    \Log::warning('Orden no encontrada o ya procesada');
                    return response()->json(['message' => 'Orden ya procesada'], 200);
                }

                foreach ($paypalOrder->hours as $hour) {

                    $startTime = Carbon::createFromFormat('H:i', $hour);
                    $endTime = $startTime->copy()->addHour();

                    $reservation = Reservation::create([
                        'user_id' => $paypalOrder->user_id,
                        'consultant_id' => $paypalOrder->consultant_id,
                        'reservation_date' => $paypalOrder->reservation_date,
                        'start_time' => $startTime->format('H:i'),
                        'end_time' => $endTime->format('H:i'),
                        'reservation_status' => 'confirmada',
                        'payment_status' => 'pagado',
                        'total_amount' => $paypalOrder->total_amount
                    ]);

                    \App\Models\ReservationDetail::create([
                        'reservation_id' => $reservation->id,
                        'transaction_id' => $transactionId,
                        'payment_status' => $status,
                        'amount' => $paypalOrder->total_amount,
                        'response_json' => json_encode($payload)
                    ]);
                }

                $paypalOrder->update([
                    'status' => 'completed',
                    'paypal_order_id' => $paypalOrderId
                ]);

                \Log::info('Reservas creadas y orden actualizada');

                $this->sendReservationEmail($paypalOrder);
                $this->sendReservationWhatsApp($paypalOrder);

                return response()->json([
                    'success' => true,
                    'message' => 'Pago confirmado y reservas creadas'
                ], 200);
            }

            \Log::info('Evento no relevante, ignorado.');

            return response()->json(['message' => 'Evento ignorado'], 200);

        } catch (\Exception $e) {

            \Log::error('ERROR EN WEBHOOK', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error en webhook'
            ], 500);
        }
    }

    private function sendReservationEmail($paypalOrder)
    {
        try {

            $user = User::find($paypalOrder->user_id);

            if (!$user || !$user->email) {
                \Log::warning('Usuario sin email válido');
                return;
            }

            Mail::to($user->email)
                ->send(new ReservationConfirmedMail($paypalOrder, $user));

            \Log::info('Correo de confirmación enviado correctamente');

        } catch (\Exception $e) {

            \Log::error('Error enviando correo', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function sendReservationWhatsApp($paypalOrder)
    {
        try {

            $user = User::find($paypalOrder->user_id);

            if (!$user || !$user->teléfono) {
                \Log::warning('Usuario sin teléfono válido');
                return;
            }

            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.whatsapp_from');

            $twilio = new Client($sid, $token);

            $messageBody = "✅ *Reserva Confirmada*\n\n"
                . "📅 Fecha: {$paypalOrder->reservation_date}\n"
                . "🕒 Horas: " . implode(', ', $paypalOrder->hours) . "\n"
                . "💵 Total: {$paypalOrder->total_amount} USD\n\n"
                . "Gracias por tu confianza 🙌";

            $twilio->messages->create(
                "whatsapp:{$user->teléfono}",
                [
                    "from" => $from,
                    "body" => $messageBody
                ]
            );

            \Log::info('WhatsApp enviado correctamente');

        } catch (\Exception $e) {

            \Log::error('Error enviando WhatsApp', [
                'error' => $e->getMessage()
            ]);
        }
    }

}
