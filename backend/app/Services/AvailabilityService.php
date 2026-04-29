<?php
namespace App\Services;
use App\Models\Branch;
use App\Models\Professional;
use App\Models\Schedule;
use App\Models\Reservation;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Obtener slots disponibles para una fecha
     */
    public function getAvailableSlots($branchId, $professionalId, $date, $serviceId = null): Collection
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        
        $schedules = Schedule::where('professional_id', $professionalId)
            ->where('branch_id', $branchId)
            ->where(function ($q) use ($dayOfWeek) {
                $q->where('day_of_week', $dayOfWeek)
                  ->orWhereNull('day_of_week');
            })
            ->where('is_active', true)
            ->get();

        if ($schedules->isEmpty()) {
            return collect([]);
        }

        $durationMinutes = 60;
        if ($serviceId) {
            $service = Service::find($serviceId);
            if ($service) {
                $durationMinutes = $service->duration_minutes;
            }
        }

        $existingReservations = Reservation::where('professional_id', $professionalId)
            ->where('branch_id', $branchId)
            ->where('reservation_date', $date)
            ->whereIn('reservation_status', ['pending', 'confirmed'])
            ->whereIn('payment_status', ['pending', 'paid'])
            ->get()
            ->map(function ($r) {
                return [
                    'start' => Carbon::parse($r->start_time)->format('H:i'),
                    'end' => Carbon::parse($r->end_time)->format('H:i'),
                ];
            });

        $slots = collect();
        
        foreach ($schedules as $schedule) {
            $start = Carbon::createFromTimeString($schedule->start_time);
            $end = Carbon::createFromTimeString($schedule->end_time);
            
            while ($start->copy()->addMinutes($durationMinutes)->lte($end)) {
                $slotStart = $start->format('H:i');
                $slotEnd = $start->copy()->addMinutes($durationMinutes)->format('H:i');
                
                $isAvailable = !$existingReservations->contains(function ($r) use ($slotStart) {
                    return $r['start'] === $slotStart;
                });

                $slots->push([
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                    'available' => $isAvailable,
                ]);

                $start->addMinutes(30);
            }
        }

        return $slots->sortBy('start_time')->values();
    }

    /**
     * Verificar si un horario específico está disponible
     */
    public function isSlotAvailable($branchId, $professionalId, $date, $startTime): bool
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        
        $hasSchedule = Schedule::where('professional_id', $professionalId)
            ->where('branch_id', $branchId)
            ->where(function ($q) use ($dayOfWeek) {
                $q->where('day_of_week', $dayOfWeek)
                  ->orWhereNull('day_of_week');
            })
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>', $startTime)
            ->where('is_active', true)
            ->exists();

        if (!$hasSchedule) {
            return false;
        }

        return !Reservation::where('professional_id', $professionalId)
            ->where('branch_id', $branchId)
            ->where('reservation_date', $date)
            ->where('start_time', $startTime)
            ->whereIn('reservation_status', ['pending', 'confirmed'])
            ->whereIn('payment_status', ['pending', 'paid'])
            ->exists();
    }
}
