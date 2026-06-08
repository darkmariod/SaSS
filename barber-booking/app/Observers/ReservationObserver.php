<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Services\GoogleCalendarService;

class ReservationObserver
{
    public function __construct(
        private readonly GoogleCalendarService $googleCalendar,
    ) {}

    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        // Solo crear evento si la reserva ya está confirmada
        if ($reservation->reservation_status === 'confirmada') {
            $eventId = $this->googleCalendar->createEvent($reservation);

            if ($eventId) {
                $reservation->updateQuietly(['google_event_id' => $eventId]);
            }
        }
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        // Detectar cambio de estado
        if ($reservation->isDirty('reservation_status')) {
            $newStatus = $reservation->reservation_status;
            $oldStatus = $reservation->getOriginal('reservation_status');

            // Cancelada -> eliminar evento
            if ($newStatus === 'cancelada') {
                $this->googleCalendar->deleteEvent($reservation);

                if ($reservation->google_event_id) {
                    $reservation->updateQuietly(['google_event_id' => null]);
                }

                return;
            }

            // Confirmada (desde pendiente) -> crear o actualizar evento
            if ($newStatus === 'confirmada' && $oldStatus !== 'confirmada') {
                if ($reservation->google_event_id) {
                    $this->googleCalendar->updateEvent($reservation);
                } else {
                    $eventId = $this->googleCalendar->createEvent($reservation);

                    if ($eventId) {
                        $reservation->updateQuietly(['google_event_id' => $eventId]);
                    }
                }

                return;
            }
        }

        // Si cambió fecha/hora/servicio y ya tiene evento, actualizar
        if (
            $reservation->google_event_id
            && (
                $reservation->isDirty('start_time')
                || $reservation->isDirty('end_time')
                || $reservation->isDirty('reservation_date')
                || $reservation->isDirty('service_id')
            )
        ) {
            $this->googleCalendar->updateEvent($reservation);
        }
    }

    /**
     * Handle the Reservation "deleted" event.
     */
    public function deleted(Reservation $reservation): void
    {
        if ($reservation->google_event_id) {
            $this->googleCalendar->deleteEvent($reservation);
        }
    }
}
