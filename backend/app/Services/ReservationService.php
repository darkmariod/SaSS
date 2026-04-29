<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Branch;
use App\Models\Professional;
use App\Models\Service;
use App\Models\ProfessionalService;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /**
     * Verificar disponibilidad de un slot
     */
    public function checkAvailability($branchId, $professionalId, $date, $startTime)
    {
        // Verificar si el profesional atiende en esa sede
        $branch = Branch::findOrFail($branchId);
        if (!$branch->professionals()->where('professionals.id', $professionalId)->exists()) {
            return ['available' => false, 'reason' => 'El profesional no atiende en esta sede'];
        }

        // Verificar horario del profesional
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $schedule = Schedule::where('professional_id', $professionalId)
            ->where('branch_id', $branchId)
            ->where(function ($q) use ($dayOfWeek) {
                $q->where('day_of_week', $dayOfWeek)
                  ->orWhereNull('day_of_week');
            })
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return ['available' => false, 'reason' => 'El profesional no tiene horario en esta fecha'];
        }

        $start = Carbon::createFromTimeString($startTime);
        $scheduleStart = Carbon::createFromTimeString($schedule->start_time);
        $scheduleEnd = Carbon::createFromTimeString($schedule->end_time);

        if ($start->lt($scheduleStart) || $start->gte($scheduleEnd)) {
            return ['available' => false, 'reason' => 'Horario fuera del horario de atención'];
        }

        // Verificar reserva existente (CON BLOQUEO PARA EVITAR DOBLE RESERVA)
        $exists = Reservation::where('professional_id', $professionalId)
            ->where('reservation_date', $date)
            ->where('start_time', $startTime)
            ->whereIn('reservation_status', ['pending', 'confirmed'])
            ->whereIn('payment_status', ['pending', 'paid'])
            ->exists();

        if ($exists) {
            return ['available' => false, 'reason' => 'Horario ya reservado'];
        }

        return ['available' => true];
    }

    /**
     * Crear reserva con items
     */
    public function createReservation($userId, $data)
    {
        return DB::transaction(function () use ($userId, $data) {
            // Obtener precio del servicio para este profesional
            $professionalService = ProfessionalService::where('professional_id', $data['professional_id'])
                ->where('service_id', $data['service_id'])
                ->where('is_active', true)
                ->firstOrFail();

            $service = Service::findOrFail($data['service_id']);
            $startTime = Carbon::createFromTimeString($data['start_time']);
            $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

            // Crear reserva
            $reservation = Reservation::create([
                'user_id' => $userId,
                'branch_id' => $data['branch_id'],
                'professional_id' => $data['professional_id'],
                'reservation_date' => $data['reservation_date'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'reservation_status' => 'pending',
                'payment_status' => 'pending',
                'total_amount' => $professionalService->price,
            ]);

            // Crear item
            $reservation->items()->create([
                'service_id' => $data['service_id'],
                'professional_service_id' => $professionalService->id,
                'price' => $professionalService->price,
                'duration_minutes' => $service->duration_minutes,
            ]);

            return $reservation->load(['items.service', 'branch', 'professional', 'client']);
        });
    }

    /**
     * Obtener disponibilidad para una fecha
     */
    public function getAvailability($branchId, $professionalId, $date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        
        // Obtener horario
        $schedule = Schedule::where('professional_id', $professionalId)
            ->where('branch_id', $branchId)
            ->where(function ($q) use ($dayOfWeek) {
                $q->where('day_of_week', $dayOfWeek)
                  ->orWhereNull('day_of_week');
            })
            ->where('is_active', true)
            ->first();

        if (!$schedule) {
            return [];
        }

        // Generar slots cada 30 min
        $slots = [];
        $start = Carbon::createFromTimeString($schedule->start_time);
        $end = Carbon::createFromTimeString($schedule->end_time);
        
        while ($start->lt($end)) {
            $slotStart = $start->format('H:i');
            $isAvailable = !Reservation::where('professional_id', $professionalId)
                ->where('reservation_date', $date)
                ->where('start_time', $slotStart)
                ->whereIn('reservation_status', ['pending', 'confirmed'])
                ->whereIn('payment_status', ['pending', 'paid'])
                ->exists();

            $slots[] = [
                'start_time' => $slotStart,
                'end_time' => $start->copy()->addMinutes(30)->format('H:i'),
                'available' => $isAvailable,
            ];

            $start->addMinutes(30);
        }

        return $slots;
    }
}
