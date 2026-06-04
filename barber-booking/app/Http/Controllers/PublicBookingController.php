<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Service;
use App\Models\Transfer;
use App\Models\Reservation;
use App\Models\BarberShop;
use App\Models\BarberProfile;
use App\Models\ReservationDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Notifications\ReservationWhatsApp;
use App\Mail\ReservationNotification;

class PublicBookingController extends Controller
{
    public function show(string $slug, Request $request): Response
    {
        $shop = BarberShop::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $services = $shop->services()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('service_type')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        $barbers = $shop->barbers()
            ->with('user:id,name,email,phone')
            ->where('is_active', true)
            ->get();

        // ?b=barber_profile_id → pre-seleccionar barbero
        $preselectedBarber = null;
        if ($request->has('b')) {
            $preselectedBarber = $barbers->firstWhere('id', (int) $request->b);
        }

        return Inertia::render('PublicBooking/Shop', [
            'shop' => $shop,
            'services' => $services,
            'barbers' => $barbers,
            'preselectedBarber' => $preselectedBarber,
        ]);
    }

    public function shop(string $slug, Request $request): Response
    {
        $shop = BarberShop::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $services = $shop->services()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('service_type')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        $barbers = $shop->barbers()
            ->with('user:id,name,email,phone')
            ->where('is_active', true)
            ->get();

        // ?b=barber_profile_id → pre-seleccionar barbero
        $preselectedBarber = null;
        if ($request->has('b')) {
            $preselectedBarber = $barbers->firstWhere('id', (int) $request->b);
        }

        return Inertia::render('PublicShop/Index', [
            'shop' => $shop,
            'services' => $services,
            'barbers' => $barbers,
            'preselectedBarber' => $preselectedBarber,
        ]);
    }

    public function availability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'addon_service_id' => ['nullable', 'exists:services,id'],
            'barber_profile_id' => ['required', 'exists:barber_profiles,id'],
            'date' => ['required', 'date'],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $barberProfile = BarberProfile::with('availabilities')->findOrFail($data['barber_profile_id']);

        if ($service->service_type !== 'option' && $service->service_type !== 'main') {
            return response()->json([
                'message' => 'El servicio seleccionado no es reservable.',
                'slots' => [],
            ], 422);
        }

        if ((int) $service->barber_shop_id !== (int) $barberProfile->barber_shop_id) {
            return response()->json([
                'message' => 'El servicio no pertenece a la barbería del barbero.',
                'slots' => [],
            ], 422);
        }

        $addonService = null;

        if (! empty($data['addon_service_id'])) {
            $addonService = Service::findOrFail($data['addon_service_id']);

            if ($addonService->service_type !== 'addon') {
                return response()->json([
                    'message' => 'El servicio adicional seleccionado no es válido.',
                    'slots' => [],
                ], 422);
            }

            if ((int) $addonService->barber_shop_id !== (int) $barberProfile->barber_shop_id) {
                return response()->json([
                    'message' => 'El servicio adicional no pertenece a esta barbería.',
                    'slots' => [],
                ], 422);
            }
        }

        $totalDuration = (int) $service->duration_minutes + (int) ($addonService?->duration_minutes ?? 0);

        if ($totalDuration <= 0) {
            return response()->json([
                'message' => 'La duración del servicio no es válida.',
                'slots' => [],
            ], 422);
        }

        $date = Carbon::parse($data['date'])->startOfDay();

        if ($date->isPast() && ! $date->isToday()) {
            return response()->json([
                'message' => 'No puedes reservar en una fecha pasada.',
                'slots' => [],
            ], 422);
        }

        $dayOfWeek = $date->dayOfWeek;

        $availability = $barberProfile->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (! $availability) {
            return response()->json([
                'date' => $date->toDateString(),
                'duration_minutes' => $totalDuration,
                'slots' => [],
            ]);
        }

        $existingReservations = Reservation::query()
            ->where('consultant_id', $barberProfile->user_id)
            ->whereDate('reservation_date', $date->toDateString())
            ->whereIn('reservation_status', ['pendiente', 'confirmada'])
            ->get();

        $slots = [];

        $start = Carbon::parse($date->toDateString() . ' ' . $availability->start_time);
        $end = Carbon::parse($date->toDateString() . ' ' . $availability->end_time);

        while ($start->copy()->addMinutes($totalDuration)->lte($end)) {
            $slotStart = $start->copy();
            $slotEnd = $start->copy()->addMinutes($totalDuration);

            $hasConflict = $existingReservations->contains(function ($reservation) use ($slotStart, $slotEnd) {
                $reservationDate = $reservation->reservation_date instanceof Carbon
                    ? $reservation->reservation_date->toDateString()
                    : Carbon::parse($reservation->reservation_date)->toDateString();

                $reservationStart = Carbon::parse($reservationDate . ' ' . $reservation->start_time);
                $reservationEnd = Carbon::parse($reservationDate . ' ' . $reservation->end_time);

                return $slotStart->lt($reservationEnd) && $slotEnd->gt($reservationStart);
            });

            if (! $hasConflict && $slotStart->isFuture()) {
                $slots[] = [
                    'start_time' => $slotStart->format('H:i'),
                    'end_time' => $slotEnd->format('H:i'),
                    'label' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                    'duration_minutes' => $totalDuration,
                ];
            }

            $start->addMinutes($totalDuration);
        }

        return response()->json([
            'date' => $date->toDateString(),
            'duration_minutes' => $totalDuration,
            'slots' => $slots,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'barber_shop_id' => ['required', 'exists:barber_shops,id'],
            'service_id' => ['required', 'exists:services,id'],
            'addon_service_id' => ['nullable', 'exists:services,id'],
            'barber_profile_id' => ['required', 'exists:barber_profiles,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'reservation_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'payment_option' => ['nullable', 'string', 'in:today,at_appointment'],
        ]);

        $shop = BarberShop::findOrFail($data['barber_shop_id']);
        $service = Service::findOrFail($data['service_id']);
        $barberProfile = BarberProfile::findOrFail($data['barber_profile_id']);

        if ((int) $service->barber_shop_id !== (int) $shop->id) {
            return response()->json([
                'message' => 'El servicio no pertenece a esta barbería.',
            ], 422);
        }

        if ($service->service_type !== 'option' && $service->service_type !== 'main') {
            return response()->json([
                'message' => 'El servicio seleccionado no es reservable.',
            ], 422);
        }

        if ((int) $barberProfile->barber_shop_id !== (int) $shop->id) {
            return response()->json([
                'message' => 'El barbero no pertenece a esta barbería.',
            ], 422);
        }

        $addonService = null;

        if (! empty($data['addon_service_id'])) {
            $addonService = Service::findOrFail($data['addon_service_id']);

            if ($addonService->service_type !== 'addon') {
                return response()->json([
                    'message' => 'El servicio adicional seleccionado no es válido.',
                ], 422);
            }

            if ((int) $addonService->barber_shop_id !== (int) $shop->id) {
                return response()->json([
                    'message' => 'El servicio adicional no pertenece a esta barbería.',
                ], 422);
            }
        }

        $start = Carbon::parse($data['reservation_date'] . ' ' . $data['start_time']);
        $totalDuration = (int) $service->duration_minutes + (int) ($addonService?->duration_minutes ?? 0);
        $totalAmount = (float) $service->price + (float) ($addonService?->price ?? 0);

        if ($totalDuration <= 0) {
            return response()->json([
                'message' => 'La duración del servicio no es válida.',
            ], 422);
        }

        $end = $start->copy()->addMinutes($totalDuration);

        if ($start->isPast()) {
            return response()->json([
                'message' => 'No puedes reservar en una fecha u hora pasada.',
            ], 422);
        }

        $reservation = DB::transaction(function () use ($data, $shop, $service, $addonService, $barberProfile, $start, $end, $totalAmount) {
            $existing = Reservation::where('consultant_id', $barberProfile->user_id)
                ->whereDate('reservation_date', $start->toDateString())
                ->whereIn('reservation_status', ['pendiente', 'confirmada'])
                ->lockForUpdate()
                ->get();

            $conflict = $existing->first(function ($reservation) use ($start, $end) {
                $resDate = $reservation->reservation_date instanceof Carbon
                    ? $reservation->reservation_date->toDateString()
                    : Carbon::parse($reservation->reservation_date)->toDateString();

                $resStart = Carbon::parse($resDate . ' ' . $reservation->start_time);
                $resEnd = Carbon::parse($resDate . ' ' . $reservation->end_time);

                return $start->lt($resEnd) && $end->gt($resStart);
            });

            if ($conflict) {
                throw ValidationException::withMessages(['time' => 'Horario no disponible']);
            }

            $reservation = Reservation::create([
                'barber_shop_id' => $shop->id,
                'service_id' => $service->id,
                'addon_service_id' => $addonService?->id,
                'user_id' => null,
                'consultant_id' => $barberProfile->user_id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'reservation_date' => $start->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'reservation_status' => 'pendiente',
                'total_amount' => $totalAmount,
                'payment_status' => 'pendiente',
            ]);

            $paymentMethod = $data['payment_option'] === 'today' ? 'bank_transfer' : 'cash';

            ReservationDetail::create([
                'reservation_id' => $reservation->id,
                'payment_method' => $paymentMethod,
                'payment_status' => 'pendiente',
                'amount' => $totalAmount,
                'response_json' => [
                    'source' => 'public_booking_flow',
                    'service_id' => $service->id,
                    'addon_service_id' => $addonService?->id,
                    'payment_option' => $data['payment_option'] ?? 'at_appointment',
                    'requires_payment' => $data['payment_option'] === 'today',
                ],
                'paid_at' => null,
            ]);

            return $reservation->load([
                'barberShop',
                'service',
                'addonService',
                'consultant',
                'detail',
            ]);
        });

        // Disparar notificación por email al dueño y al cliente
        $emailSent = false;
        try {
            $mail = new ReservationNotification($reservation);
            Mail::to($shop->owner->email)->send($mail);
            $emailSent = true;

            // Si el cliente dejó email, también se lo enviamos
            if ($reservation->customer_email) {
                Mail::to($reservation->customer_email)->send($mail);
            }
        } catch (\Exception $e) {
            \Log::error('Error sending reservation email: ' . $e->getMessage());
        }

        // Disparar notificación WhatsApp al dueño (wa.me link)
        try {
            $shop->owner->notify(new ReservationWhatsApp($reservation));
        } catch (\Exception $e) {
            \Log::error('Error sending WhatsApp notification: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Reserva creada correctamente.',
            'reservation' => $reservation,
        ], 201);
    }

    public function uploadReceipt(Request $request, Reservation $reservation): JsonResponse
    {
        if ($reservation->customer_name !== $request->input('customer_name') ||
            $reservation->customer_phone !== $request->input('customer_phone')) {
            abort(403, 'No autorizado');
        }

        $data = $request->validate([
            'receipt_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'reference' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        if ($reservation->reservation_status === 'cancelada') {
            return response()->json([
                'message' => 'No puedes subir comprobante a una reserva cancelada.',
            ], 422);
        }

        $path = $data['receipt_image']->store('receipts', 'public');

        $reservation = DB::transaction(function () use ($reservation, $path, $data) {
            $reservation->update([
                'payment_status' => 'comprobante_subido',
            ]);

            $detail = $reservation->detail;

            if (! $detail) {
                $detail = ReservationDetail::create([
                    'reservation_id' => $reservation->id,
                    'payment_method' => 'bank_transfer',
                    'payment_status' => 'comprobante_subido',
                    'amount' => $reservation->total_amount,
                    'receipt_image' => $path,
                    'response_json' => [
                        'source' => 'receipt_upload',
                        'receipt_uploaded_at' => now()->toDateTimeString(),
                    ],
                ]);
            } else {
                $detail->update([
                    'payment_status' => 'comprobante_subido',
                    'receipt_image' => $path,
                    'response_json' => array_merge($detail->response_json ?? [], [
                        'receipt_uploaded_at' => now()->toDateTimeString(),
                    ]),
                ]);
            }

            $transfer = Transfer::updateOrCreate(
                [
                    'reservation_id' => $reservation->id,
                ],
                [
                    'barber_shop_id' => $reservation->barber_shop_id,
                    'reference' => $data['reference'] ?? null,
                    'amount' => $reservation->total_amount,
                    'status' => 'pending',
                    'receipt_image_path' => $path,
                    'confirmed_by' => null,
                    'confirmed_at' => null,
                    'notes' => 'Transferencia creada desde comprobante público.',
                ]
            );

            $reservation->update([
                'transfer_id' => $transfer->id,
            ]);

            return $reservation->fresh([
                'barberShop',
                'service',
                'addonService',
                'consultant',
                'detail',
                'transfer',
            ]);
        });

        return response()->json([
            'message' => 'Comprobante subido correctamente. La barbería revisará tu pago.',
            'reservation' => $reservation,
            'receipt_url' => asset('storage/' . $path),
        ]);
    }
}
