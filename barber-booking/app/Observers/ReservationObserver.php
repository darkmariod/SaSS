<?php

namespace App\Observers;

use App\Jobs\SyncReservationToGoogleCalendar;
use App\Models\Reservation;

class ReservationObserver
{
    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        // Only mirror to Google once the reservation is confirmed.
        if ($reservation->reservation_status === 'confirmada') {
            SyncReservationToGoogleCalendar::dispatch('sync', $reservation->id);
        }
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        // Detect status transitions.
        if ($reservation->isDirty('reservation_status')) {
            $newStatus = $reservation->reservation_status;
            $oldStatus = $reservation->getOriginal('reservation_status');

            // Cancelled -> delete the mirrored event.
            if ($newStatus === 'cancelada') {
                if ($reservation->google_event_id) {
                    SyncReservationToGoogleCalendar::dispatch(
                        'delete',
                        googleEventId: $reservation->google_event_id,
                        googleCalendarId: $reservation->google_calendar_id,
                    );

                    $reservation->updateQuietly([
                        'google_event_id' => null,
                        'google_calendar_id' => null,
                    ]);
                }

                return;
            }

            // Confirmed (from another status) -> create or update the event.
            if ($newStatus === 'confirmada' && $oldStatus !== 'confirmada') {
                SyncReservationToGoogleCalendar::dispatch('sync', $reservation->id);

                return;
            }
        }

        // Date/time/service changed on an already-mirrored reservation -> update.
        if (
            $reservation->google_event_id
            && (
                $reservation->isDirty('start_time')
                || $reservation->isDirty('end_time')
                || $reservation->isDirty('reservation_date')
                || $reservation->isDirty('service_id')
            )
        ) {
            SyncReservationToGoogleCalendar::dispatch('sync', $reservation->id);
        }
    }

    /**
     * Handle the Reservation "deleted" event.
     */
    public function deleted(Reservation $reservation): void
    {
        if ($reservation->google_event_id) {
            SyncReservationToGoogleCalendar::dispatch(
                'delete',
                googleEventId: $reservation->google_event_id,
                googleCalendarId: $reservation->google_calendar_id,
            );
        }
    }
}
