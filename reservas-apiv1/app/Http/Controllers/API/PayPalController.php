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
    public function createOrder(Request $request)
    {
        try {
            $user = $request->user();
            $clientRoleId = 3;

            if ($user->rol_id != $clientRoleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo clientes pueden realizar pagos'
                ], 403);
            }

            $validated = $request->validate([
                'consultant_id' => 'required|exists:users,id',
                'service_id' => 'required|exists:services,id',
                'date' => 'required|date',
                'hours' => 'required|array|min:1'
            ]);

            $date = $validated['date'];
            $consultantId = $validated['consultant_id'];
            $serviceId = $validated['service_id'];
            $hours = collect($validated['hours'])->sort()->values();

            // Obtener el servicio para calcular el precio
            $service = \App\Models\Service::findOrFail($serviceId);
            
            // Verificar que el servicio pertenezca al consultor
            if ($service->consultant_id != $consultantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'El servicio no pertenece a este consultor'
                ], 400);
            }

            // Fecha no puede ser pasada
            if ($date < now()->format('Y-m-d')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes reservar fechas pasadas'
                ], 400);
            }

            // Validar consecutividad
            for ($i = 0; $i < count($hours) - 1; $i++) {
                $current = Carbon::createFromFormat('H:i', $hours[$i]);
                $next = Carbon::createFromFormat('H:i', $hours[$i + 1]);

                if ($current->copy()->addHour()->format('H:i') !== $next->format('H:i')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Las horas deben ser consecutivas'
                    ], 400);
                }
            }

            // Validar disponibilidad
            $existingReservations = Reservation::where('consultant_id', $consultantId)
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

            $totalAmount = count($hours) * $service->price_per_hour;

            // Generar referencia UUID
            $reference = Str::uuid()->toString();

            // Guardar metadata en tabla paypal_orders
            $paypalOrder = PayPalOrder::create([
                'reference' => $reference,
                'user_id' => $user->id,
                'consultant_id' => $consultantId,
                'service_id' => $serviceId,
                'reservation_date' => $date,
                'hours' => $hours,
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]);

            // Crear orden PayPal
            $provider = new PayPal();
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
                    "return_url" => url('/api/paypal/success'),
                    "cancel_url" => url('/api/paypal/cancel')
                ]
            ]);

            // Extraer links
            $approveUrl = null;
            $links = $order['links'] ?? [];
            foreach ($links as $link) {
                if ($link['rel'] === 'approve') {
                    $approveUrl = $link['href'];
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Orden creada correctamente',
                'data' => [
                    'reference' => $reference,
                    'approve_url' => $approveUrl,
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

    public function webhook(Request $request)
    {
        try {
            \Log::info('================ PAYPAL WEBHOOK START ================');

            $payload = $request->all();
            \Log::info('Payload:', $payload);

            $eventType = $payload['event_type'] ?? null;
            \Log::info('Event Type:', ['event_type' => $eventType]);

            // SI ES APPROVED → CAPTURAR AUTOMÁTICAMENTE
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

            // SI ES CAPTURE COMPLETED → CREAR RESERVAS
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

                try {
                    // USAR TRANSACCIÓN DB PARA EVITAR DOBLE RESERVA
                    \Illuminate\Support\Facades\DB::transaction(function () use ($paypalOrder, $transactionId, $status, $payload, $reference) {

                        foreach ($paypalOrder->hours as $hour) {

                            $startTime = Carbon::createFromFormat('H:i', $hour);
                            $endTime = $startTime->copy()->addHour();

                            // SELECT FOR UPDATE - BLOQUEA EL SLOT PARA EVITAR CONDICIÓN DE CARRERA
                            $exists = Reservation::where('consultant_id', $paypalOrder->consultant_id)
                                ->whereDate('reservation_date', $paypalOrder->reservation_date)
                                ->where('start_time', $hour)
                                ->where('reservation_status', 'confirmada')
                                ->where('payment_status', 'pagado')
                                ->lockForUpdate()
                                ->exists();

                            if ($exists) {
                                throw new \Exception("El slot {$hour} ya está reservado");
                            }

                            $reservation = Reservation::create([
                                'user_id' => $paypalOrder->user_id,
                                'consultant_id' => $paypalOrder->consultant_id,
                                'service_id' => $paypalOrder->service_id,
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
                            'paypal_order_id' => $paypalOrderId ?? null
                        ]);
                    });

                    \Log::info('Reservas creadas y orden actualizada');

                    $this->sendReservationEmail($paypalOrder);
                    $this->sendReservationWhatsApp($paypalOrder);

                    return response()->json([
                        'success' => true,
                        'message' => 'Pago confirmado y reservas creadas'
                    ], 200);

                } catch (\Exception $e) {
                    \Log::error('Error en transacción de reserva: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error al procesar reservas: ' . $e->getMessage()
                    ], 500);
                }
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

    public function success(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Pago completado correctamente'
        ]);
    }

    private function sendReservationEmail($paypalOrder)
    {
        try {
            $user = User::find($paypalOrder->user_id);
            $consultant = User::find($paypalOrder->consultant_id);

            if ($user && $consultant) {
                Mail::to($user->email)->send(new ReservationConfirmedMail([
                    'user_name' => $user->nombres . ' ' . $user->apellidos,
                    'consultant_name' => $consultant->nombres . ' ' . $consultant->apellidos,
                    'reservation_date' => $paypalOrder->reservation_date,
                    'hours' => $paypalOrder->hours,
                    'total_amount' => $paypalOrder->total_amount
                ]));
            }
        } catch (\Exception $e) {
            \Log::error('Error al enviar email: ' . $e->getMessage());
        }
    }

    private function sendReservationWhatsApp($paypalOrder)
    {
        try {
            $user = User::find($paypalOrder->user_id);
            
            if ($user && $user->telefono) {
                $twilioSid = config('services.twilio.sid');
                $twilioToken = config('services.twilio.token');
                $twilioFrom = config('services.twilio.from');

                if ($twilioSid && $twilioToken && $twilioFrom) {
                    $client = new Client($twilioSid, $twilioToken);
                    
                    $message = "Hola {$user->nombres}, tu reserva ha sido confirmada. " .
                              "Fecha: {$paypalOrder->reservation_date}, " .
                              "Horas: " . implode(', ', $paypalOrder->hours) . ", " .
                              "Total: $" . number_format($paypalOrder->total_amount, 2);

                    $client->messages->create(
                        "whatsapp:" . $user->telefono,
                        [
                            'from' => "whatsapp:" . $twilioFrom,
                            'body' => $message
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error al enviar WhatsApp: ' . $e->getMessage());
        }
    }
}
